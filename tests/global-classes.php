<?php
// SPDX-License-Identifier: GPL-2.0-or-later
require __DIR__ . '/bootstrap.php';
$mode = $argv[1] ?? 'fallback';
if ($mode !== 'absence') { require __DIR__ . '/global-classes-double.php'; }
function is_admin() { return $GLOBALS['admin']; }
function is_preview() { return $GLOBALS['wp_preview']; }
function get_option($key) { return $GLOBALS['site']['options'][$key] ?? false; }
function get_post_type($id) { return $GLOBALS['site']['posts'][$id]['type'] ?? false; }
function get_post_status($id) { return $GLOBALS['site']['posts'][$id]['status'] ?? false; }
function get_post_meta($id, $key, $single) { return $GLOBALS['site']['posts'][$id]['meta'][$key] ?? ''; }
$GLOBALS['write_attempts'] = 0;
function forbidden_write() { ++$GLOBALS['write_attempts']; throw new RuntimeException('Forbidden persistent write'); }
function update_post_meta() { forbidden_write(); }
function update_option() { forbidden_write(); }
function wp_update_post() { forbidden_write(); }
function add_post_meta() { forbidden_write(); }
function delete_post_meta() { forbidden_write(); }
function apply_filters($hook, $value, ...$args) {
    if ($hook === 'wpml_default_language') { return $GLOBALS['default_language']; }
    if ($hook === 'wpml_current_language') { return $GLOBALS['language']; }
    if ($hook !== 'wpml_object_id') { throw new RuntimeException('Unexpected WPML hook'); }
    check($args[0] === 'elementor_library' && $args[1] === false, 'Strict native mapping contract');
    return $GLOBALS['mapping'][$value][$args[2]] ?? null;
}
function fixture() {
    $GLOBALS['fault'] = '';
    $GLOBALS['kit_constructions'] = 0;
    $GLOBALS['admin'] = false; $GLOBALS['wp_preview'] = false; $GLOBALS['preview'] = false;
    $GLOBALS['language'] = 'fr'; $GLOBALS['default_language'] = 'en';
    $GLOBALS['mapping'] = [801 => ['en' => 801, 'fr' => 802], 802 => ['en' => 801, 'fr' => 802]];
    $post = ['type' => 'elementor_library', 'status' => 'publish', 'meta' => [
        '_elementor_template_type' => 'kit', '_elementor_global_classes_labels' => [], '_elementor_global_classes_order' => [],
    ]];
    $GLOBALS['site'] = ['options' => ['elementor_active_kit' => '801'], 'posts' => [801 => $post, 802 => $post]];
    $GLOBALS['site']['posts'][801]['meta']['_elementor_global_classes_labels'] = ['g-alpha' => 'synthetic-title', 'g-beta' => 'synthetic-input'];
    $GLOBALS['site']['posts'][801]['meta']['_elementor_global_classes_order'] = ['g-alpha', 'g-beta'];
    $GLOBALS['active_kit'] = new \Elementor\Core\Kits\Documents\Kit(['post_id' => 802]);
    \Elementor\Plugin::$instance = (object) [
        'preview' => new class { public function is_editor_or_preview() { return $GLOBALS['preview']; } },
        'kits_manager' => new class { public function get_active_kit() { return $GLOBALS['active_kit']; } },
        // The normal document getter would redirect even an explicit source ID.
        'documents' => new class { public function get($id) { throw new RuntimeException('Redirected getter must never be used'); } },
    ];
}
function verify($input, $expected, $label) {
    $snapshot = $GLOBALS['site'] ?? [];
    $before = $input;
    $actual = call_user_func($GLOBALS['global_class_filter'], $input);
    check($actual === $expected, $label);
    check($input === $before, 'Input immutable: ' . $label);
    check(($GLOBALS['site'] ?? []) === $snapshot, 'Persistent state immutable: ' . $label);
    check($GLOBALS['write_attempts'] === 0, 'No attempted persistent writes: ' . $label);
}
load_plugin();
check(count($GLOBALS['adapter_filters']) === 4, 'Four translation filters retained');
check(is_callable($GLOBALS['global_class_filter']), 'Frontend fallback registered');
$input = [3 => 'g-alpha', 7 => 'e-local', 11 => 'g-beta', 13 => 'custom-class', 15 => 'g-unknown'];
$expected = [3 => 'synthetic-title', 7 => 'e-local', 11 => 'synthetic-input', 13 => 'custom-class', 15 => 'g-unknown'];
if ($mode === 'absence') {
    verify($input, $input, 'Missing Elementor is a no-op');
} else {
    fixture();
    switch ($mode) {
        case 'fallback':
            verify($input, $expected, 'Missing translated labels recover from explicit source kit');
            verify($expected, $expected, 'Repeated filtering preserves resolved names');
            break;
        case 'native':
            $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_labels'] = ['g-alpha' => 'native-title', 'g-beta' => 'native-input'];
            $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_order'] = ['g-alpha', 'g-beta'];
            verify(['native-title', 'native-input', 'e-local'], ['native-title', 'native-input', 'e-local'], 'Already resolved native output retained');
            verify($input, $input, 'Unresolved target-owned declarations are not replaced');
            break;
        case 'target_owned':
            $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_labels'] = ['g-alpha' => 'owned-title'];
            $partial = $expected; $partial[3] = 'g-alpha';
            verify($input, $partial, 'Unordered target declaration wins while missing class falls back');
            $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_order'] = ['g-beta'];
            verify($input, $input, 'Target order without label also retained');
            fixture();
            $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_labels'] = ['g-custom' => 'g-alpha', 'g-other' => 'synthetic-input'];
            verify($input, $input, 'Native label resembling an ID and target label collision preserved');
            fixture(); $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_post_ids'] = ['g-alpha' => 910];
            verify($input, $partial, 'Target class post without labels/order remains owned');
            break;
        case 'mapping':
            foreach ([null, 0, 999, '801garbage', true] as $id) {
                fixture(); $GLOBALS['mapping'][802]['en'] = $id;
                verify($input, $input, 'Unknown or unrelated source mapping');
            }
            fixture(); $GLOBALS['mapping'][801]['fr'] = 999;
            verify($input, $input, 'Reverse mapping must identify the same translated kit');
            foreach ([null, '', 'all', 'en', ['fr']] as $lang) {
                fixture(); $GLOBALS['language'] = $lang;
                verify($input, $input, 'Invalid or source-language context');
            }
            fixture(); $GLOBALS['default_language'] = null;
            verify($input, $input, 'Missing WPML default language');
            break;
        case 'preview':
            $GLOBALS['preview'] = true; verify($input, $input, 'Elementor editor/preview excluded');
            $GLOBALS['preview'] = false; $GLOBALS['wp_preview'] = true;
            verify($input, $input, 'WordPress preview excluded');
            break;
        case 'admin': case 'rest': case 'ajax': case 'cli':
            if ($mode === 'admin') { $GLOBALS['admin'] = true; }
            else { define(['rest' => 'REST_REQUEST', 'ajax' => 'DOING_AJAX', 'cli' => 'WP_CLI'][$mode], true); }
            verify($input, $input, 'Non-frontend context excluded');
            break;
        case 'identity':
            $GLOBALS['fault'] = 'redirect'; verify($input, $input, 'Unexpected source constructor identity');
            fixture(); $GLOBALS['active_kit'] = new \Elementor\Core\Kits\Documents\Kit(['post_id' => 801]);
            verify($input, $input, 'Already using source kit');
            fixture(); $GLOBALS['site']['options']['elementor_active_kit'] = '999';
            verify($input, $input, 'Mapped source must match configured CSS kit');
            break;
        case 'metadata':
            foreach ([801, 802] as $id) {
                foreach (['type' => 'post', 'status' => 'trash'] as $key => $value) {
                    fixture(); $GLOBALS['site']['posts'][$id][$key] = $value;
                    verify($input, $input, 'Only published library kits supported');
                }
                fixture(); $GLOBALS['site']['posts'][$id]['meta']['_elementor_template_type'] = 'page';
                verify($input, $input, 'Kit marker required');
            }
            foreach (['bad', [new stdClass()]] as $value) {
                fixture(); $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_order'] = $value;
                verify($input, $input, 'Malformed target order');
            }
            fixture(); $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_labels'] = '';
            verify($input, $expected, 'Missing target labels are supported');
            foreach (['invalid', ['g-alpha' => 0], ['g-alpha' => '910invalid']] as $map) {
                fixture(); $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_post_ids'] = $map;
                verify($input, $input, 'Unknown target class-post contract');
            }
            break;
        case 'labels':
            foreach (['', 'two tokens', 'x"onclick', '.selector', '9invalid', 'g-other', "line\nbreak", 'synthetic-input'] as $label) {
                fixture(); $GLOBALS['site']['posts'][801]['meta']['_elementor_global_classes_labels']['g-alpha'] = $label;
                $result = $expected; $result[3] = 'g-alpha';
                if ($label === 'synthetic-input') { $result[11] = 'g-beta'; }
                verify($input, $result, 'Unsafe or ambiguous source label is not substituted');
            }
            fixture(); $GLOBALS['site']['posts'][801]['meta']['_elementor_global_classes_order'] = ['g-alpha'];
            $partial = $expected; $partial[11] = 'g-beta';
            verify($input, $partial, 'Unordered source labels are not applied');
            break;
        case 'exceptions':
            foreach (['constructor', 'repository', 'repository_shape'] as $fault_name) {
                fixture(); $GLOBALS['fault'] = $fault_name;
                verify($input, $input, 'Native failure returns original output');
                $GLOBALS['fault'] = ''; verify($input, $expected, 'Failure does not poison later calls');
            }
            break;
        case 'reentry':
            $GLOBALS['fault'] = 'reentry'; verify($input, $expected, 'Constructor reentry terminates safely');
            check($GLOBALS['nested_result'] === ['g-alpha'], 'Reentrant input unchanged');
            break;
        case 'malformed':
            foreach ([null, 'g-alpha', [], ['g-alpha', 12], ['g-alpha', ['nested']]] as $value) {
                verify($value, $value, 'Unknown input untouched');
            }
            foreach (['garbage', ['g-alpha' => ['label']], ['not-global' => 'label']] as $labels) {
                fixture(); $GLOBALS['site']['posts'][802]['meta']['_elementor_global_classes_labels'] = $labels;
                verify($input, $input, 'Unknown target label contract untouched');
            }
            break;
        case 'context_switch':
            verify($input, $expected, 'Translated embedded context');
            $GLOBALS['active_kit'] = new \Elementor\Core\Kits\Documents\Kit(['post_id' => 801]);
            verify($input, $input, 'Source context in same request does not reuse fallback');
            $GLOBALS['active_kit'] = new \Elementor\Core\Kits\Documents\Kit(['post_id' => 802]);
            verify($input, $expected, 'Returning to embedded context');
            break;
        case 'unknown':
            \Elementor\Plugin::$instance->preview = new stdClass();
            verify($input, $input, 'Unknown preview API');
            fixture(); $GLOBALS['preview'] = null; verify($input, $input, 'Unknown preview result');
            fixture(); \Elementor\Plugin::$instance->kits_manager = new stdClass();
            verify($input, $input, 'Unknown kit manager API');
            fixture(); $GLOBALS['active_kit'] = new stdClass(); verify($input, $input, 'Unknown kit object');
            break;
        default: throw new RuntimeException('Unknown scenario');
    }
}
echo 'PASS global-classes-' . $mode . ': ' . $GLOBALS['checks'] . " synthetic checks\n";
