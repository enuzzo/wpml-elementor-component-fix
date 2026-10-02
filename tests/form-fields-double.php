<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Original synthetic model of documented field paths/field_id, not WPML code.
// This checks registration semantics only; it cannot certify WPML's real importer.
class Synthetic_Form_Fields {
    private function read($settings, $path) {
        foreach (explode('>', $path) as $part) {
            if (!is_array($settings) || !array_key_exists($part, $settings)) { return null; }
            $settings = $settings[$part];
        }
        return is_string($settings) ? $settings : null;
    }
    public function get($node, $fields) {
        $strings = [];
        foreach ($fields as $field) {
            $value = $this->read($node['settings'], $field['field']);
            if ($value !== null) {
                // Invented identity format, deliberately not a WPML implementation.
                $strings[] = new WPML_PB_String($value, $node['id'] . '/' . ($field['field_id'] ?? $field['field']), $field['type'], $field['editor_type'] ?? 'LINE');
            }
        }
        return $strings;
    }
    public function update($node, $fields, WPML_PB_String $translation) {
        foreach ($fields as $field) {
            $identity = $node['id'] . '/' . ($field['field_id'] ?? $field['field']);
            if ($identity !== $translation->get_name() || $this->read($node['settings'], $field['field']) === null) { continue; }
            $value = &$node['settings'];
            foreach (explode('>', $field['field']) as $part) { $value = &$value[$part]; }
            $value = $translation->get_value();
            unset($value);
        }
        return $node;
    }
}
