<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Utils\Rector\Rector\ChangeApiPlatformResourceGetterToProperty\ChangeApiPlatformResourceGetterToPropertyRector;
use Utils\Rector\Rector\ReadonlyClassWithoutGetter\ReadonlyClassWithoutGetterRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(RenameClassRector::class);
    $rectorConfig->rule(ReadonlyClassWithoutGetterRector::class);
    $rectorConfig->importNames();
};
