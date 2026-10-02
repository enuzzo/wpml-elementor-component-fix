<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Invented data only. These tests do not contain vendor code or load a CMS.
require __DIR__ . '/bootstrap.php';
$mode = $argv[1];
$GLOBALS['master_mode'] = $mode;
if ($mode === 'typed') {
    class WPML_Elementor_Translatable_Nodes {
        public function initialize_nodes_to_translate() {}
        public function get($node_id, $element): array { return []; }
        public function update($node_id, $element, WPML_PB_String $string) { return $element; }
        public function get_string_name($node_id, $field, $settings) { return ''; }
    }
} elseif ($mode !== 'absent') {
    require __DIR__ . '/master-nodes-double.php';
}
if ($mode === 'combined') {
    $GLOBALS['native_mode'] = 'broken';
    require __DIR__ . '/native-double.php';
}
function origin_config() {
    $result = [];
    foreach (['e-heading' => 'title', 'e-paragraph' => 'paragraph'] as $widget => $property) {
        $legacy = $property . '>value>content>value';
        $result[$widget] = ['conditions' => ['widgetType' => $widget], 'fields' => [
            ['field' => $legacy, 'type' => 'Synthetic text', 'editor_type' => 'VISUAL'],
            ['field' => $property . '>value', 'field_id' => $legacy, 'type' => 'Synthetic text', 'editor_type' => 'VISUAL'],
            ['field' => 'link>value>destination>value', 'type' => 'Synthetic link', 'editor_type' => 'LINK'],
        ]];
    }
    return $result;
}
function master_node($widget, $property, $text = 'Synthetic <strong>master</strong> &amp; café ’') {
    return ['id' => 'invented-master-' . $property, 'elType' => 'widget', 'widgetType' => $widget, 'settings' => [
        $property => ['$$type' => 'overridable', 'value' => [
            'override_key' => 'invented-' . $property,
            'origin_value' => ['$$type' => 'escaped-html', 'value' => $text, 'keep' => true],
            'keep_binding' => ['original' => true],
        ]],
        'link' => ['value' => ['destination' => ['value' => 'https://example.test/untouched']]],
    ], 'elements' => []];
}
load_plugin();
$filter = $GLOBALS['adapter_filter'];
$original = origin_config();
$GLOBALS['master_base_config'] = $original;
$mapped = $filter($original);
if (in_array($mode, ['typed', 'absent'], true)) {
    check($mapped === $original, 'Unknown/absent native contract is unchanged');
} elseif ($mode === 'unknown') {
    $variants = [];
    $variants[] = ['integration-class' => ['Custom_Master_Handler']];
    $variants[] = ['fields_in_item' => []];
    $variants[] = ['conditions' => ['widgetType' => 'other']];
    $variants[] = ['extra' => true];
    foreach ($variants as $extra) {
        $input = $original;
        $input['e-heading'] = array_replace($input['e-heading'], $extra);
        check($filter($input)['e-heading'] === $input['e-heading'], 'Custom/unknown config untouched');
    }
    $fields = $original['e-heading']['fields'];
    foreach ([
        array_merge($fields, [$fields[0]]),
        array_merge($fields, [['field' => 'title>value>origin_value>value', 'type' => 'Native', 'editor_type' => 'LINE']]),
        array_merge($fields, [['field' => 'other', 'field_id' => 'title>value', 'type' => 'Collision']]),
        [array_merge($fields[0], ['extra' => true])],
        [array_merge($fields[0], ['field_id' => 'unknown'])],
        [array_merge($fields[0], ['editor_type' => 'LINK'])],
        [['field' => 'title>value', 'type' => 'Missing editor']],
        ['invalid'],
    ] as $alternative) {
        $input = $original;
        $input['e-heading']['fields'] = $alternative;
        check($filter($input)['e-heading'] === $input['e-heading'], 'Ambiguous/unknown field contract untouched');
    }
    check($filter(null) === null, 'Non-array untouched');
} else {
    check(WPML_Elementor_Translatable_Nodes::$get_calls === 0, 'Registration never probes a fabricated element');
    foreach ($original as $widget => $config) {
        check($mapped[$widget]['fields'] === $config['fields'], 'No competing field paths added');
        check($mapped[$widget]['integration-class'] === ['Netmilk_WPML_Master_Origins'], 'Scoped native delegate registered');
    }
    check($original === origin_config(), 'Input registration immutable');
    check($filter($mapped) === $mapped, 'Registration idempotent');
    if ($mode === 'registration') {
        echo 'PASS master-registration: ' . $GLOBALS['checks'] . " checks\n";
        exit(0);
    }
    $module = new Netmilk_WPML_Master_Origins();
    $native = new WPML_Elementor_Translatable_Nodes();
    foreach (['e-heading' => 'title', 'e-paragraph' => 'paragraph'] as $widget => $property) {
        $node = master_node($widget, $property);
        $id = $node['id'];
        $name = $native->get_string_name($id, $original[$widget]['fields'][1], $node);
        $translation = new WPML_PB_String('Translated <em>master</em> &amp; déjà', $name, 'Synthetic text', 'VISUAL');
        if ($mode === 'throws') {
            $existing = [new WPML_PB_String('Keep', 'unrelated', '', 'LINE')];
            check($module->get($id, $node, $existing) === $existing, 'Native exception preserves input strings');
            check($module->update($id, $node, $translation) === [null, null], 'Native exception yields no update');
            continue;
        }
        if ($mode === 'cache') {
            WPML_Elementor_Translatable_Nodes::$cache_enabled = true;
            WPML_Elementor_Translatable_Nodes::$cached_element = null;
        }
        $before = $node;
        $strings = $native->get($id, $node);
        $texts = array_values(array_filter($strings, function ($s) use ($name) { return $s->get_name() === $name; }));
        $expected_count = in_array($mode, ['fixed', 'import_broken'], true) ? 2 : 1;
        check(count($texts) === $expected_count, 'No new duplicate text identity; native incoming duplicates preserved');
        check($texts[0]->get_value() === $node['settings'][$property]['value']['origin_value']['value'], 'Exact markup/entities preserved');
        check($texts[0]->editor === 'VISUAL' && $texts[0]->title === 'Synthetic text', 'Native string metadata preserved');
        check(count($strings) === $expected_count + 1, 'Native link extraction preserved');
        if ($mode === 'fixed') {
            check($module->get($id, $node, []) === [], 'Working native plain/HTML round trips bypass adapter');
            check($module->update($id, $node, $translation) === [null, null], 'Working native import bypassed');
        }
        $result = $native->update($id, $node, $translation);
        $expected = $node;
        if ($mode !== 'unsafe') { $expected['settings'][$property]['value']['origin_value']['value'] = $translation->get_value(); }
        check($result === $expected, 'Only expected text update accepted; type and all metadata retained');
        check($node === $before, 'Source node immutable');
        check($native->update($id, $result, $translation) === $result, 'Repeated import idempotent');
        $unrelated = new WPML_PB_String('Ignore', 'unknown-name', '', 'LINE');
        check($native->update($id, $node, $unrelated) === $node, 'Unknown identity untouched');
        check($module->update($id, $node, new WPML_PB_String([], $name, '', 'LINE')) === [null, null], 'Non-string import value rejected');
        if ($mode === 'roundtrip') {
            $edited = master_node($widget, $property, 'Edited source <b>second cycle</b>');
            $fresh = $native->get($id, $edited);
            $fresh_text = array_values(array_filter($fresh, function ($s) use ($name) { return $s->get_name() === $name; }));
            check(count($fresh_text) === 1 && $fresh_text[0]->get_value() === 'Edited source <b>second cycle</b>', 'Fresh extraction observes edited source with same identity');
            $second = new WPML_PB_String('Second translated cycle', $name, '', 'VISUAL');
            $updated = $native->update($id, $edited, $second);
            $wanted = $edited;
            $wanted['settings'][$property]['value']['origin_value']['value'] = $second->get_value();
            check($updated === $wanted, 'Second source-edit/import cycle changes only native text leaf');
        }
        if ($mode === 'cache') {
            check(WPML_Elementor_Translatable_Nodes::$cached_element === $before, 'Native cache contains actual original element, not synthetic probe');
            WPML_Elementor_Translatable_Nodes::$cache_enabled = false;
        }
        if ($mode !== 'fixed') {
            check($module->get($id, $node, [$texts[0]]) === [$texts[0]], 'Adapter never adds an existing identity');
        }
        foreach ([null, ['$$type' => 'override', 'value' => ['override_key' => 'child', 'override_value' => null]], ['$$type' => 'escaped-html', 'value' => []]] as $origin) {
            $unknown = $node;
            $unknown['settings'][$property]['value']['origin_value'] = $origin;
            check($module->get($id, $unknown, []) === [], 'Null/forwarded/malformed origin unchanged');
            check($module->update($id, $unknown, $translation) === [null, null], 'No fabricated leaf or instance override');
        }
        foreach (['', '0'] as $empty) {
            $falsey = master_node($widget, $property, $empty);
            check($module->get($id, $falsey, []) === [], 'Native falsey-string omission explicitly preserved');
        }
        $inactive = $node;
        $inactive['synthetic_inactive'] = [$property];
        check($module->get($id, $inactive, []) === [], 'Native inactive field remains excluded');
        check($module->update($id, $inactive, $translation) === [null, null], 'Inactive field not imported');
        foreach (['string', 'html-v3'] as $type) {
            $supported = $node;
            $supported['settings'][$property]['value']['origin_value'] = ['$$type' => $type, 'value' => $type === 'string' ? 'Native text' : ['content' => ['value' => 'Native HTML']]];
            check($module->get($id, $supported, []) === [], 'Other native origin types unchanged');
            check($module->update($id, $supported, $translation) === [null, null], 'Other native origin types not adapted');
        }
        $ordinary = $node;
        $ordinary['settings'][$property] = ['$$type' => 'escaped-html', 'value' => 'Ordinary'];
        check($module->get($id, $ordinary, []) === [], 'Ordinary unexposed text not adapted');
    }
    if ($mode === 'combined') {
        $input = array_merge($original, config(), ['e-form' => ['fields' => [['field' => 'form-name', 'type' => 'Synthetic form']]]]);
        $result = $filter($input);
        check($result['e-component']['integration-class'][0] === 'Netmilk_WPML_Escaped_HTML_Overrides', 'Component adapter coexists');
        check($result['e-form']['fields'][1]['field_id'] === 'form-name', 'Form correction coexists');
        check($result['e-heading']['integration-class'] === ['Netmilk_WPML_Master_Origins'], 'Master module coexists');
        check($filter($result) === $result, 'Combined filtering idempotent');
    }
}
echo 'PASS master-' . $mode . ': ' . $GLOBALS['checks'] . " checks\n";
