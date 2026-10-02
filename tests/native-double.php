<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Invented minimal contract double; NOT a copy of the WPML implementation.
namespace WPML\PB\Elementor\V4\Component;
class Overrides {
    private function readable($value) {
        $type = $value['$$type'] ?? null;
        if ($type === 'escaped-html') {
            if (!in_array($GLOBALS['native_mode'], ['fixed', 'import_broken', 'text_only'], true)) { return null; }
            if ($GLOBALS['native_mode'] === 'text_only' && strpos($value['value'], '<') !== false) { return null; }
            return is_string($value['value']) ? $value['value'] : null;
        }
        if ($type === 'string') { return is_string($value['value']) ? $value['value'] : null; }
        if ($type === 'link') { return $value['value']['url']['value'] ?? null; }
        if ($type === 'html-v3') { return $value['value']['content']['value'] ?? null; }
        return null;
    }
    public function get($id, $element, $strings) {
        if ($GLOBALS['native_mode'] === 'throws') { throw new \RuntimeException('Unknown contract'); }
        foreach ($element['settings']['component_instance']['value']['overrides']['value'] ?? [] as $item) {
            $value = $item['value']['override_value'];
            $text = $this->readable($value);
            if ($text !== null) {
                $name = $id . '/' . $item['value']['override_key'];
                $strings[] = new \WPML_PB_String($text, $name, 'Synthetic field', $value['$$type'] === 'link' ? 'LINK' : 'LINE');
            }
        }
        return $strings;
    }
    public function update($id, $element, \WPML_PB_String $string) {
        foreach ($element['settings']['component_instance']['value']['overrides']['value'] ?? [] as $key => $item) {
            if ($string->get_name() !== $id . '/' . $item['value']['override_key']) { continue; }
            $value = $item['value']['override_value'];
            if ($this->readable($value) === null) { continue; }
            if ($value['$$type'] === 'escaped-html' && $GLOBALS['native_mode'] === 'import_broken') { return [$key, $item]; }
            if ($value['$$type'] === 'link') {
                $item['value']['override_value']['value']['url']['value'] = $string->get_value();
            } elseif ($value['$$type'] === 'html-v3') {
                $item['value']['override_value']['value']['content']['value'] = $string->get_value();
            } else {
                $item['value']['override_value']['value'] = $string->get_value();
            }
            return [$key, $item];
        }
        return [null, null];
    }
}
