<?php

declare(strict_types=1);

$finder = \PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/Classes',
        __DIR__ . '/Tests',
    ]);

return (new \PhpCsFixer\Config())
    ->setRules([
        '@PER-CS2x0' => true,
        '@PER-CS2x0:risky' => true,
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
