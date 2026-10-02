<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Original test doubles only. No WordPress, WPML or Elementor vendor code.
define('ABSPATH', __DIR__);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});
class WPML_PB_String {
    public $value, $name, $title, $editor;
    public function __construct($value, $name, $title, $editor) {
        $this->value = $value;
        $this->name = $name;
        $this->title = $title;
        $this->editor = $editor;
    }
    public function get_value() { return $this->value; }
    public function get_name() { return $this->name; }
}
function add_filter($hook, $callback, $priority) {
    if ($hook !== 'wpml_elementor_widgets_to_translate' || $priority !== 100) {
        throw new RuntimeException('Unexpected registration');
    }
    $GLOBALS['adapter_filter'] = $callback;
}
$GLOBALS['checks'] = 0;
function check($condition, $label) {
    if (!$condition) { throw new RuntimeException($label); }
    ++$GLOBALS['checks'];
}
function load_plugin() {
    require __DIR__ . '/../plugin/netmilk-wpml-component-compat/netmilk-wpml-component-compat.php';
}
function config() {
    return [
        'unrelated-widget' => ['fields' => ['keep-this']],
        'e-component' => ['integration-class' => [
            '\\WPML\\PB\\Elementor\\V4\\Component\\Overrides', 'Another_Handler',
        ]],
    ];
}
function node($id, $values) {
    $items = [];
    foreach ($values as $key => $value) {
        $items[] = ['$$type' => 'override', 'value' => [
            'override_key' => $key, 'override_value' => $value,
            'extra_metadata' => ['keep' => true],
        ]];
    }
    return ['id' => $id, 'widgetType' => 'e-component', 'settings' => [
        'component_instance' => ['value' => ['overrides' => ['value' => $items]]],
    ]];
}
