<?php

declare(strict_types=1);

namespace Utils\Rector\Tests\Rector\ReadonlyClassWithoutGetter;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ReadonlyClassWithoutGetterRectorTest extends AbstractRectorTestCase
{
    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @dataProvider provideData
     */
    public function test(string $filePath): void
    {
        $this->doTestFile($filePath);
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/config.php';
    }
}
