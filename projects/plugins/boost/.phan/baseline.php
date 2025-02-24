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
    // PhanTypeArraySuspiciousNullable : 15+ occurrences
    // PhanPluginDuplicateConditionalNullCoalescing : 10+ occurrences
    // PhanTypeArraySuspicious : 9 occurrences
    // PhanTypeMismatchArgument : 8 occurrences
    // PhanTypeMismatchReturnProbablyReal : 5 occurrences
    // PhanUndeclaredConstant : 5 occurrences
    // PhanUndeclaredFunction : 4 occurrences
    // PhanPossiblyUndeclaredVariable : 3 occurrences
    // PhanTypeMismatchPropertyDefault : 3 occurrences
    // PhanTypePossiblyInvalidDimOffset : 3 occurrences
    // PhanUndeclaredClassConstant : 3 occurrences
    // PhanNoopNew : 2 occurrences
    // PhanPluginUseReturnValueInternalKnown : 2 occurrences
    // PhanTypeMismatchArgumentNullableInternal : 2 occurrences
    // PhanCoalescingNeverNull : 1 occurrence
    // PhanImpossibleTypeComparisonInGlobalScope : 1 occurrence
    // PhanRedefineFunction : 1 occurrence
    // PhanRedundantCondition : 1 occurrence
    // PhanTypeComparisonToArray : 1 occurrence
    // PhanTypeInvalidUnaryOperandIncOrDec : 1 occurrence
    // PhanTypeMismatchArgumentInternal : 1 occurrence
    // PhanTypeMismatchProperty : 1 occurrence
    // PhanTypeMissingReturn : 1 occurrence
    // PhanUndeclaredClassMethod : 1 occurrence
    // PhanUndeclaredClassReference : 1 occurrence
    // PhanUndeclaredFunctionInCallable : 1 occurrence

    // Currently, file_suppressions and directory_suppressions are the only supported suppressions
    'file_suppressions' => [
        'app/admin/class-admin.php' => ['PhanNoopNew'],
        'app/admin/class-config.php' => ['PhanTypeMismatchArgument'],
        'app/data-sync/Minify_Excludes_State_Entry.php' => ['PhanTypeMismatchReturnProbablyReal'],
        'app/data-sync/Performance_History_Entry.php' => ['PhanPluginDuplicateConditionalNullCoalescing', 'PhanTypeArraySuspicious'],
        'app/lib/class-cli.php' => ['PhanPluginDuplicateConditionalNullCoalescing', 'PhanTypeMismatchArgument'],
        'app/lib/critical-css/Critical_CSS_State.php' => ['PhanPluginDuplicateConditionalNullCoalescing', 'PhanTypeArraySuspiciousNullable'],
        'app/lib/critical-css/source-providers/providers/Provider.php' => ['PhanTypeMismatchArgumentInternal'],
        'app/lib/minify/Concatenate_CSS.php' => ['PhanPluginUseReturnValueInternalKnown', 'PhanTypeMismatchArgument'],
        'app/lib/minify/Concatenate_JS.php' => ['PhanPluginUseReturnValueInternalKnown', 'PhanPossiblyUndeclaredVariable', 'PhanTypeInvalidUnaryOperandIncOrDec', 'PhanTypeMismatchArgument', 'PhanTypeMismatchArgumentNullableInternal', 'PhanTypePossiblyInvalidDimOffset'],
        'app/lib/minify/Dependency_Path_Mapping.php' => ['PhanUndeclaredConstant'],
        'app/lib/minify/functions-helpers.php' => ['PhanTypeMismatchArgumentNullableInternal', 'PhanUndeclaredClassMethod', 'PhanUndeclaredClassReference', 'PhanUndeclaredConstant'],
        'app/modules/Modules_Setup.php' => ['PhanTypeMismatchPropertyDefault'],
        'app/modules/image-guide/Image_Guide_Proxy.php' => ['PhanPluginDuplicateConditionalNullCoalescing'],
        'app/modules/image-size-analysis/data-sync/Image_Size_Analysis_Action_Fix.php' => ['PhanPossiblyUndeclaredVariable', 'PhanRedundantCondition'],
        'app/modules/optimizations/critical-css/CSS_Proxy.php' => ['PhanPluginDuplicateConditionalNullCoalescing'],
        'app/modules/optimizations/page-cache/Page_Cache_Setup.php' => ['PhanTypeMismatchArgument', 'PhanTypeMismatchReturnProbablyReal', 'PhanTypeMissingReturn'],
        'app/modules/optimizations/page-cache/pre-wordpress/Boost_Cache.php' => ['PhanTypeMismatchReturnProbablyReal'],
        'app/modules/optimizations/page-cache/pre-wordpress/Logger.php' => ['PhanCoalescingNeverNull', 'PhanPluginDuplicateConditionalNullCoalescing'],
        'app/modules/optimizations/page-cache/pre-wordpress/Request.php' => ['PhanPluginDuplicateConditionalNullCoalescing', 'PhanTypeMismatchPropertyDefault'],
        'app/modules/optimizations/page-cache/pre-wordpress/storage/File_Storage.php' => ['PhanTypeMismatchArgument'],
        'app/modules/optimizations/render-blocking-js/class-render-blocking-js.php' => ['PhanTypeMismatchProperty', 'PhanTypeMismatchPropertyDefault'],
        'compatibility/elementor.php' => ['PhanUndeclaredClassConstant'],
        'compatibility/page-optimize.php' => ['PhanUndeclaredFunction', 'PhanUndeclaredFunctionInCallable'],
        'compatibility/score-prompt.php' => ['PhanImpossibleTypeComparisonInGlobalScope', 'PhanTypeComparisonToArray'],
        'compatibility/web-stories.php' => ['PhanUndeclaredClassConstant'],
        'compatibility/woocommerce.php' => ['PhanTypeArraySuspicious'],
        'jetpack-boost.php' => ['PhanNoopNew'],
        'tests/bootstrap.php' => ['PhanRedefineFunction', 'PhanTypeMismatchReturnProbablyReal'],
    ],
    // 'directory_suppressions' => ['src/directory_name' => ['PhanIssueName1', 'PhanIssueName2']] can be manually added if needed.
    // (directory_suppressions will currently be ignored by subsequent calls to --save-baseline, but may be preserved in future Phan releases)
];
