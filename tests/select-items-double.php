<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Original contract model. Invented identities/dispatch, no vendor implementation.
namespace WPML\PB\Elementor\Modules;
class ModuleWithItemsFromConfig {
    private $collection;
    private $fields;
    public function __construct($collection, array $fields) { $this->collection=$collection; $this->fields=$fields; }
    public function get_items_field() { return $this->collection; }
    public function get_fields() { return [$this->fields[0]['field']]; }
    public function get_title($field) { return $this->fields[0]['type']; }
    public function get_editor_type($field) { return $this->fields[0]['editor_type']; }
    public function get_field_path($key) { return array_merge(explode('>',$this->collection),[$key]); }
    public function get_items($element) {
        if (isset($element['settings'][$this->collection])) { return $element['settings'][$this->collection]; }
        if (in_array($GLOBALS['select_mode'],['fixed','nested_only','import_broken','collision'],true)) {
            return $element['settings']['options']['value'];
        }
        // Deliberate missing-key read to exercise bounded native-probe diagnostics.
        $settings=$element['settings'];
        return $settings[$this->collection];
    }
    private function name($id,$item) {
        if ($GLOBALS['select_mode']==='collision') { return 'synthetic-collision'; }
        $key=$item['_id']??null;
        if ($key===null && in_array($GLOBALS['select_mode'],['fixed','import_broken'],true)) {
            $key=$item['value']['value']['value'];
        }
        return 'invented-option/'.$id.'/'.$key;
    }
    public function get($id,$element,$strings) {
        if ($GLOBALS['select_mode']==='throws') { throw new \RuntimeException('Invented failure'); }
        if ($GLOBALS['select_mode']==='unexpected_warning') { trigger_error('Unrelated synthetic problem',E_USER_WARNING); }
        foreach ($this->get_items($element) as $item) {
            $label=$item['value']['key']['value'];
            $native_zero=in_array($GLOBALS['select_mode'],['fixed','import_broken'],true);
            if ($label==='' || ($label==='0' && !$native_zero)) { continue; }
            $strings[]=new \WPML_PB_String($label,$this->name($id,$item),$this->fields[0]['type'],$this->fields[0]['editor_type']);
        }
        return $strings;
    }
    public function update($id,$element,\WPML_PB_String $string) {
        if ($GLOBALS['select_mode']==='import_broken' && !isset($element['settings'][$this->collection])) { return [null,null]; }
        foreach ($this->get_items($element) as $key=>$item) {
            if ($this->name($id,$item)!==$string->get_name()) { continue; }
            $item['value']['key']['value']=$string->get_value();
            if ($GLOBALS['select_mode']==='unsafe') { $item['value']['value']['value']='damaged'; }
            return [$key,$item];
        }
        return [null,null];
    }
}
