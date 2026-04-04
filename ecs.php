<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\PSR12\Sniffs\Classes\ClassDeclarationSniff;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests', __DIR__ . '/examples'])
    ->withRootFiles()
    ->withPreparedSets(psr12: true, common: true, symplify: true)
    ->withSkip([
        NoUnusedImportsFixer::class => null,
        ClassDeclarationSniff::class => null,
    ])
    ->withCache(directory: '.php-cs-fixer.cache');
