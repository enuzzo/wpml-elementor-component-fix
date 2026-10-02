<?php
// SPDX-License-Identifier: GPL-2.0-or-later
require __DIR__ . '/bootstrap.php';
$GLOBALS['native_mode'] = $argv[1] ?? 'broken';
require __DIR__ . '/native-double.php';
load_plugin();
$config = config();
$mapped = $GLOBALS['adapter_filter']($config);
$keep = in_array($GLOBALS['native_mode'], ['fixed', 'throws'], true);
check(($mapped === $config) === $keep, 'Native support decision: ' . $GLOBALS['native_mode']);
check($GLOBALS['adapter_filter']($config) === $mapped, 'Repeated registration stable');
check($GLOBALS['adapter_filter']($mapped) === $mapped, 'Adapter never doubled');
check($GLOBALS['adapter_filter'](null) === null, 'Invalid config unchanged');
check($GLOBALS['adapter_filter']([]) === [], 'Missing registration unchanged');
check($mapped['unrelated-widget'] === $config['unrelated-widget'], 'Unrelated widgets untouched');
check($mapped['e-component']['integration-class'][1] === 'Another_Handler', 'Other handlers retained');
$custom = $config;
$custom['e-component']['integration-class'] = ['Existing_Custom_Handler'];
check($GLOBALS['adapter_filter']($custom) === $custom, 'Existing replacement retained');
if ($keep) {
    echo 'PASS ' . $GLOBALS['native_mode'] . ': ' . $GLOBALS['checks'] . " assertions\n";
    exit;
}
$class = $mapped['e-component']['integration-class'][0];
$handler = new $class();
$native = new \WPML\PB\Elementor\V4\Component\Overrides();
$all_names = [];
for ($n = 1; $n <= 4; ++$n) {
    $element = node('demo-' . $n, [
        'title' => ['$$type' => 'escaped-html', 'value' => 'An autumn <strong>scene</strong> &amp; café ’ ' . $n],
        'label' => ['$$type' => 'escaped-html', 'value' => 'Download phone version'],
        'desktop-url' => ['$$type' => 'link', 'value' => [
            'url' => ['$$type' => 'string', 'value' => 'https://example.org/original-' . $n . '.jpg'],
            'isExternal' => true, 'nofollow' => false,
        ]],
        'legacy-text' => ['$$type' => 'string', 'value' => 'Legacy text'],
        'legacy-html' => ['$$type' => 'html-v3', 'value' => ['content' => ['$$type' => 'string', 'value' => '<b>Legacy</b>']]],
        'not-text' => ['$$type' => 'number', 'value' => 12],
        'malformed-text' => ['$$type' => 'escaped-html', 'value' => ['not-a-string']],
    ]);
    $before = $element;
    // Native probing modes are already tested; use the broken baseline for adapter regression.
    $GLOBALS['native_mode'] = 'broken';
    $old = $native->get($element['id'], $element, []);
    $new = $handler->get($element['id'], $element, []);
    check(count($old) === 3, 'Baseline keeps legacy text, HTML and link');
    check(count($new) === 5, 'Recovers exactly two escaped-html texts');
    foreach ($old as $string) { check(in_array($string, $new), 'Native field identity and metadata retained'); }
    foreach ($new as $string) {
        $all_names[] = $string->get_name();
        $target = new WPML_PB_String('Translated: café & <strong>paper</strong>', $string->get_name(), $string->title, $string->editor);
        [$key, $item] = $handler->update($element['id'], $element, $target);
        $expected = $element['settings']['component_instance']['value']['overrides']['value'][$key];
        $type = $expected['value']['override_value']['$$type'];
        if ($type === 'link') {
            $expected['value']['override_value']['value']['url']['value'] = $target->get_value();
        } elseif ($type === 'html-v3') {
            $expected['value']['override_value']['value']['content']['value'] = $target->get_value();
        } else {
            $expected['value']['override_value']['value'] = $target->get_value();
        }
        check($item === $expected, 'Only intended value changed, including type preservation');
        $reordered = $element;
        $reordered['settings']['component_instance']['value']['overrides']['value'] = array_reverse($reordered['settings']['component_instance']['value']['overrides']['value']);
        [$other_key, $other_item] = $handler->update($element['id'], $reordered, $target);
        check($other_item === $expected, 'Stable identity after reordering');
        if ($type !== 'escaped-html') {
            check($native->update($element['id'], $element, $target) === [$key, $item], 'Native import behavior preserved');
        }
    }
    check($handler->update($element['id'], $element, new WPML_PB_String('x', 'missing', '', 'LINE')) === [null, null], 'Unknown translation ignored');
    check($element === $before, 'Source remains immutable');
}
check(count(array_unique($all_names)) === count($all_names), 'Distinct IDs across instances');
$empty = node('empty', []);
check($handler->get('empty', $empty, []) === [], 'Empty overrides ignored');
check($handler->get('missing', [], []) === [], 'Missing settings ignored');
echo 'PASS adapter: ' . $GLOBALS['checks'] . " assertions\n";
