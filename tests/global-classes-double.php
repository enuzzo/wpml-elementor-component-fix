<?php
// SPDX-License-Identifier: GPL-2.0-or-later
// Original synthetic contracts, not Elementor or WPML source.
namespace Elementor {
    class Plugin { public static $instance; }
}
namespace Elementor\Core\Kits\Documents {
    class Kit {
        private $id;
        public function __construct(array $data = []) {
            ++$GLOBALS['kit_constructions'];
            if ($GLOBALS['fault'] === 'constructor') { throw new \RuntimeException('Synthetic constructor failure'); }
            if ($GLOBALS['fault'] === 'reentry') {
                $GLOBALS['nested_result'] = \Netmilk_WPML_Global_Class_Labels::filter(['g-alpha']);
            }
            $this->id = $data['post_id'];
            if ($GLOBALS['fault'] === 'redirect') { $this->id = 802; }
        }
        public function get_id() { return $this->id; }
        public function get_meta($key) { return \get_post_meta($this->id, $key, true); }
    }
}
namespace Elementor\Modules\GlobalClasses {
    class Global_Classes_Repository {
        private $kit;
        public static function make($kit = null) {
            if (!$kit) { throw new \RuntimeException('Explicit kit required by test'); }
            $repo = new self(); $repo->kit = $kit; return $repo;
        }
        public function set_preview($preview = true) {
            if ($preview !== false) { throw new \RuntimeException('Frontend context required'); }
            return $this;
        }
        public function get_order() { return \get_post_meta($this->kit->get_id(), '_elementor_global_classes_order', true); }
        public function all_labels() {
            if ($GLOBALS['fault'] === 'repository') { throw new \RuntimeException('Synthetic repository failure'); }
            if ($GLOBALS['fault'] === 'repository_shape') { return new \stdClass(); }
            $map = $this->kit->get_meta('_elementor_global_classes_labels');
            $result = [];
            foreach ($this->get_order() as $id) { if (isset($map[$id])) { $result[$id] = $map[$id]; } }
            return $result;
        }
    }
}
