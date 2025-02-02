<?php

return (new PhpCsFixer\Config())
    ->setParallelConfig(PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'protected_to_private' => false,
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'match', 'parameters']],
        'native_function_invocation' => ['include' => ['@compiler_optimized', 'sprintf'], 'scope' => 'namespaced', 'strict' => true],
        'nullable_type_declaration' => true,
        'nullable_type_declaration_for_default_null_value' => true,
        'phpdoc_to_comment' => false, // LOL, this one is pointless and destructive
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        (new PhpCsFixer\Finder())
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/bin')
            ->in(__DIR__.'/tests')
            ->append([__FILE__])
    )
    ->setCacheFile('.php-cs-fixer.cache');
