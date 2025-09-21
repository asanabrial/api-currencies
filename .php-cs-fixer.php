<?php

declare(strict_types=1);

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('storage')
    ->exclude('bootstrap/cache')
    ->exclude('node_modules')
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'declare_strict_types' => true,
        'array_syntax' => ['syntax' => 'short'],
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'single_quote' => true,
        'trailing_comma_in_multiline' => true,
        'no_extra_blank_lines' => [
            'tokens' => ['extra', 'throw', 'use']
        ],
        'concat_space' => ['spacing' => 'one'],
        'cast_spaces' => true,
        'binary_operator_spaces' => true,
        'return_type_declaration' => true,
        'no_whitespace_in_blank_line' => true,
        'method_argument_space' => [
            'on_multiline' => 'ensure_fully_multiline'
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);