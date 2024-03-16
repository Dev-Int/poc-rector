<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Utils\Rector\Rector\ReadonlyClassWithoutGetter\ReadonlyClassWithoutGetterRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src/Subscription/Adapters/Controller/ApiPlatform/Resource/',
    ])
    // uncomment to reach your current PHP version
    // ->withPhpSets()
    ->withRules([
        AddVoidReturnTypeWhereNoReturnRector::class,
        RenameClassRector::class,
        ReadOnlyClassRector::class,
        ReadonlyClassWithoutGetterRector::class,
    ]);
