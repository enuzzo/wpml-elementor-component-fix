<?php
// SPDX-License-Identifier: GPL-2.0-or-later
require __DIR__ . '/bootstrap.php';
require __DIR__ . '/form-fields-double.php';
$mode = $argv[1];
if ($mode === 'combined') {
    $GLOBALS['native_mode'] = 'broken';
    require __DIR__ . '/native-double.php';
}
load_plugin();
$filter = $GLOBALS['adapter_filter'];
function form_config() {
    return [
        'e-form' => ['conditions' => ['widgetType' => 'e-form'], 'fields' => [
            ['field' => 'form-name', 'type' => 'Form: Name'],
            ['field' => 'email>value>subject>value', 'type' => 'Form: Email subject'],
        ]],
        'form' => ['fields' => [['field' => 'form_name', 'type' => 'Legacy form name']]],
    ];
}
$original = form_config();
$mapped = $filter($original);
$fields = $mapped['e-form']['fields'];
if ($mode === 'registration') {
    check(count($fields) === 3, 'Adds one nested form-name registration');
    check(array_slice($fields, 0, 2) === $original['e-form']['fields'], 'Existing paths and metadata preserved');
    check($fields[2] === ['field' => 'form-name>value', 'type' => 'Form: Name', 'field_id' => 'form-name'], 'Nested path retains native identity');
    check($original === form_config(), 'Input configuration remains immutable');
    check($mapped['form'] === $original['form'], 'Legacy form widget unchanged');
    check($filter($mapped) === $mapped && $filter($original) === $mapped, 'Repeat calls are idempotent');
    $explicit = $original;
    $explicit['e-form']['fields'][0]['field_id'] = 'form-name';
    $explicit['e-form']['fields'][0]['editor_type'] = 'LINE';
    check($filter($explicit)['e-form']['fields'][2]['editor_type'] === 'LINE', 'Explicit native metadata preserved');
    unset($explicit['e-form']['conditions']);
    check(count($filter($explicit)['e-form']['fields']) === 3, 'Implicit widget condition supported');
} elseif ($mode === 'roundtrip') {
    $handler = new Synthetic_Form_Fields();
    $ids = [];
    foreach (['Synthetic test form', 'Café &amp; apostrophe ’ <strong>test</strong>', '', '0'] as $index => $source) {
        foreach (['scalar', 'wrapped'] as $shape) {
            $name = $shape === 'scalar' ? $source : ['$$type' => 'string', 'value' => $source, 'synthetic_metadata' => 'keep'];
            $node = ['id' => 'synthetic-' . $index . '-' . $shape, 'widgetType' => 'e-form', 'settings' => ['form-name' => $name, 'unrelated' => 'keep']];
            $before = $node;
            check(count($handler->get($node, $original['e-form']['fields'])) === ($shape === 'scalar' ? 1 : 0), 'Synthetic baseline demonstrates the missing leaf');
            $strings = $handler->get($node, $fields);
            check(count($strings) === 1 && $strings[0]->get_value() === $source, 'Exactly one field for either shape');
            $ids[] = $strings[0]->get_name();
            check($strings[0]->get_name() === $node['id'] . '/form-name', 'Identity independent of the data path');
            $translation = new WPML_PB_String('Translated: ' . $source, $strings[0]->get_name(), '', 'LINE');
            $expected = $node;
            if ($shape === 'scalar') { $expected['settings']['form-name'] = $translation->get_value(); }
            else { $expected['settings']['form-name']['value'] = $translation->get_value(); }
            $updated = $handler->update($node, $fields, $translation);
            check($updated === $expected, 'Only intended text changes; wrapper type and metadata survive');
            check($handler->update($updated, $fields, $translation) === $updated, 'Repeated import is idempotent in the synthetic model');
            check($handler->update($node, $fields, new WPML_PB_String('ignored', 'unknown', '', 'LINE')) === $node, 'Unknown identity ignored');
            check($node === $before, 'Source remains immutable');
        }
    }
    check(count(array_unique($ids)) === count($ids), 'Distinct identities for distinct instances');
    foreach ([[], ['form-name' => ['$$type' => 'string']], ['form-name' => ['$$type' => 'string', 'value' => []]]] as $settings) {
        check($handler->get(['id' => 'missing', 'settings' => $settings], $fields) === [], 'Missing/non-string leaf is not manufactured');
    }
} elseif ($mode === 'native') {
    foreach (['form-name>value', 'form-name>value>future'] as $path) {
        foreach (['form-name', 'custom-identity'] as $identity) {
            $config = $original;
            $config['e-form']['fields'][] = ['field' => $path, 'field_id' => $identity, 'type' => 'Existing'];
            check($filter($config) === $config, 'Existing nested native or custom registration retained');
        }
    }
    $native = $mapped;
    unset($native['e-form']['fields'][0]);
    check($filter($native) === $native, 'Native nested-only registration retained');
} elseif ($mode === 'unknown') {
    $cases = [null, [], ['e-form' => null], ['e-form' => 'unknown'], ['e-form' => ['fields' => 'unknown']], ['e-form' => ['fields' => $original['e-form']['fields'], 'conditions' => null]]];
    foreach (['integration-class' => 'Custom_Handler', 'fields-in-item' => [], 'future' => true, 'conditions' => ['widgetType' => 'other']] as $key => $value) {
        $config = $original; $config['e-form'][$key] = $value; $cases[] = $config;
    }
    foreach (['field_id' => 'custom', 'editor_type' => 'AREA', 'future' => true, 'type' => null] as $key => $value) {
        $config = $original; $config['e-form']['fields'][0][$key] = $value; $cases[] = $config;
    }
    foreach ([null, 'form-name', ['field' => ['unknown']], ['field' => 'another-path', 'field_id' => 'form-name'], $original['e-form']['fields'][0]] as $extra) {
        $config = $original; $config['e-form']['fields'][] = $extra; $cases[] = $config;
    }
    $config = $original; unset($config['e-form']['fields'][0]); $cases[] = $config;
    foreach ($cases as $config) { check($filter($config) === $config, 'Unknown, ambiguous or missing registration left unchanged'); }
} elseif ($mode === 'combined') {
    $config = array_merge(config(), $original);
    $result = $filter($config);
    check($result['e-component']['integration-class'][0] === 'Netmilk_WPML_Escaped_HTML_Overrides', 'Component adapter still selected');
    check($result['e-form'] === $mapped['e-form'], 'Form extension independent of component adapter');
    check($filter($result) === $result, 'Combined registration idempotent');
}
echo 'PASS form-name ' . $mode . ': ' . $GLOBALS['checks'] . " synthetic assertions\n";
