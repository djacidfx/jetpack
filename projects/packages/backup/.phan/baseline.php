<?php
/**
 * This is an automatically generated baseline for Phan issues.
 * When Phan is invoked with --load-baseline=path/to/baseline.php,
 * The pre-existing issues listed in this file won't be emitted.
 *
 * This file can be updated by invoking Phan with --save-baseline=path/to/baseline.php
 * (can be combined with --load-baseline)
 */
return [
    // # Issue statistics:
    // PhanTypeMismatchReturnProbablyReal : 15+ occurrences
    // PhanTypeMismatchArgumentProbablyReal : 8 occurrences
    // PhanTypeMismatchReturn : 6 occurrences
    // PhanUndeclaredStaticMethod : 2 occurrences
    // PhanPossiblyUndeclaredVariable : 1 occurrence
    // PhanUndeclaredClassMethod : 1 occurrence

    // Currently, file_suppressions and directory_suppressions are the only supported suppressions
    'file_suppressions' => [
        'src/class-jetpack-backup.php' => ['PhanPossiblyUndeclaredVariable', 'PhanTypeMismatchArgumentProbablyReal', 'PhanTypeMismatchReturn', 'PhanTypeMismatchReturnProbablyReal', 'PhanUndeclaredClassMethod', 'PhanUndeclaredStaticMethod'],
        'src/class-rest-controller.php' => ['PhanTypeMismatchArgumentProbablyReal', 'PhanTypeMismatchReturn', 'PhanTypeMismatchReturnProbablyReal'],
    ],
    // 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
    // (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
