<?php
// SPDX-License-Identifier: GPL-2.0-or-later
$cases = [
    'scenario.php' => ['broken', 'fixed', 'throws', 'import_broken', 'text_only'],
    'unknown-contract.php' => ['final', 'typed-return', 'typed-param', 'by-reference', 'final-method', 'missing-method', 'static-method', 'extra-param', 'absent'],
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
