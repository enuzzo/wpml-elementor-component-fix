<?php
// SPDX-License-Identifier: GPL-2.0-or-later
require __DIR__ . '/bootstrap.php';
$mode = $argv[1];
$contracts = [
    'final' => 'final class Overrides { public function get($a,$b,$c) { return []; } public function update($a,$b,\\WPML_PB_String $c) { return [null,null]; } }',
    'typed-return' => 'class Overrides { public function get($a,$b,$c): array { return []; } public function update($a,$b,\\WPML_PB_String $c): array { return [null,null]; } }',
    'typed-param' => 'class Overrides { public function get($a,array $b,$c) { return []; } public function update($a,$b,\\WPML_PB_String $c) { return [null,null]; } }',
    'by-reference' => 'class Overrides { public function get($a,&$b,$c) { return []; } public function update($a,$b,\\WPML_PB_String $c) { return [null,null]; } }',
    'final-method' => 'class Overrides { final public function get($a,$b,$c) { return []; } public function update($a,$b,\\WPML_PB_String $c) { return [null,null]; } }',
    'missing-method' => 'class Overrides { public function get($a,$b,$c) { return []; } }',
    'static-method' => 'class Overrides { public static function get($a,$b,$c) { return []; } public function update($a,$b,\\WPML_PB_String $c) { return [null,null]; } }',
    'extra-param' => 'class Overrides { public function get($a,$b,$c,$d=null) { return []; } public function update($a,$b,\\WPML_PB_String $c) { return [null,null]; } }',
];
if ($mode !== 'absent') { eval('namespace WPML\\PB\\Elementor\\V4\\Component; ' . $contracts[$mode]); }
load_plugin();
check($GLOBALS['adapter_filter'](config()) === config(), 'Unknown contract must be left unchanged: ' . $mode);
echo 'PASS unchanged contract: ' . $mode . "\n";
