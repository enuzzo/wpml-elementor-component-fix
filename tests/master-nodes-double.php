<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Original, deliberately small contract model; not WPML implementation code.
// Names and dispatch are invented. Only public method signatures match the API.
class WPML_Elementor_Translatable_Nodes {
    private $config;
    public static $get_calls = 0;
    public static $cache_enabled = false;
    public static $cached_element = null;
    public function initialize_nodes_to_translate() {
        $this->config = ($GLOBALS['adapter_filter'])($GLOBALS['master_base_config']);
    }
    public function get_string_name($node_id, $field, $settings) {
        return 'synthetic/' . $settings['widgetType'] . '/' . $node_id . '/' . ($field['field_id'] ?? $field['field']);
    }
    private function configuration($element) {
        if ($this->config === null) { $this->initialize_nodes_to_translate(); }
        return $this->config[$element['widgetType']] ?? ['fields' => []];
    }
    private function location($element, $field, $import = false) {
        if (self::$cache_enabled && self::$cached_element === null) { self::$cached_element = $element; }
        $active = self::$cache_enabled ? self::$cached_element : $element;
        $path = explode('>', $field['field']);
        if (in_array($path[0], $active['synthetic_inactive'] ?? [], true)) { return null; }
        $value = $element['settings'];
        foreach ($path as $part) { $value = is_array($value) ? ($value[$part] ?? null) : null; }
        if (is_string($value)) { return $path; }
        $wrapper = $element['settings'][$path[0]] ?? null;
        if (($wrapper['$$type'] ?? null) !== 'overridable') { return null; }
        $origin = $wrapper['value']['origin_value'] ?? null;
        if (!is_array($origin)) { return null; }
        $type = $origin['$$type'] ?? null;
        $mode = $GLOBALS['master_mode'];
        $supported = $type === 'string' || $type === 'html-v3';
        if ($type === 'escaped-html') {
            $supported = $mode === 'fixed' || ($mode === 'import_broken' && !$import)
                || ($mode === 'text_only' && strpos($origin['value'], '<') === false);
        }
        if (!$supported) { return null; }
        $result = [$path[0], 'value', 'origin_value', 'value'];
        if ($type === 'html-v3') { $result = array_merge($result, ['content', 'value']); }
        $value = $element['settings'];
        foreach ($result as $part) { $value = is_array($value) ? ($value[$part] ?? null) : null; }
        return is_string($value) ? $result : null;
    }
    public function get($node_id, $element) {
        ++self::$get_calls;
        if ($GLOBALS['master_mode'] === 'throws') { throw new RuntimeException('Synthetic native failure'); }
        $config = $this->configuration($element);
        $strings = [];
        foreach ($config['fields'] as $field) {
            $path = $this->location($element, $field);
            if ($path === null) { continue; }
            $value = $element['settings'];
            foreach ($path as $part) { $value = $value[$part]; }
            // The observed native generic handler omits both falsey strings.
            if ($value === '' || $value === '0') { continue; }
            $strings[] = new WPML_PB_String($value, $this->get_string_name($node_id, $field, $element), $field['type'], $field['editor_type']);
        }
        foreach ($config['integration-class'] ?? [] as $class) {
            $strings = (new $class())->get($node_id, $element, $strings);
        }
        return $strings;
    }
    public function update($node_id, $element, WPML_PB_String $string) {
        $config = $this->configuration($element);
        foreach ($config['fields'] as $field) {
            $path = $this->location($element, $field, true);
            if ($path === null || $string->get_name() !== $this->get_string_name($node_id, $field, $element)) { continue; }
            $leaf = &$element['settings'];
            foreach ($path as $part) { $leaf = &$leaf[$part]; }
            $leaf = $string->get_value();
            unset($leaf);
            if ($GLOBALS['master_mode'] === 'unsafe') { $element['unexpected'] = true; }
        }
        foreach ($config['integration-class'] ?? [] as $class) {
            $instance = new $class();
            list($key, $item) = $instance->update($node_id, $element, $string);
            if ($key === null || $item === null) { continue; }
            $leaf = &$element['settings'];
            foreach ($instance->get_field_path($key) as $part) { $leaf = &$leaf[$part]; }
            $leaf = $item;
            unset($leaf);
        }
        return $element;
    }
}
