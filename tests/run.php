<?php
// SPDX-License-Identifier: GPL-2.0-or-later
$cases = [
    'scenario.php' => ['broken', 'fixed', 'throws', 'import_broken', 'text_only'],
    'unknown-contract.php' => ['final', 'typed-return', 'typed-param', 'nullable-param', 'by-reference', 'final-method', 'missing-method', 'static-method', 'extra-param', 'absent'],
    'form-name.php' => ['registration', 'roundtrip', 'native', 'unknown', 'combined'],
    'master-origins.php' => ['registration', 'roundtrip', 'fixed', 'text_only', 'import_broken', 'throws', 'unsafe', 'unknown', 'combined', 'absent', 'typed', 'cache', 'legacy-source'],
    'select-options.php' => ['broken', 'fixed', 'nested_only', 'import_broken', 'throws', 'unexpected_warning', 'unsafe', 'collision', 'unknown', 'absent', 'typed', 'combined'],
];
$count = 0;
foreach ($cases as $file => $modes) {
    foreach ($modes as $mode) {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/' . $file) . ' ' . escapeshellarg($mode);
        passthru($command, $status);
        if ($status !== 0) { exit($status); }
        ++$count;
    }
}
echo "PASS: $count isolated scenarios (synthetic contract tests, not a live CMS)\n";
