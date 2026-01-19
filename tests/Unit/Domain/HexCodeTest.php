<?php

declare(strict_types=1);

/**
 * This file is part of storyblok/php-image-service.
 *
 * (c) Storyblok GmbH <info@storyblok.com>
 * in cooperation with SensioLabs Deutschland <info@sensiolabs.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Storyblok\ImageService\Tests\Unit\Domain;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Storyblok\ImageService\Domain\HexCode;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(HexCode::class)]
final class HexCodeTest extends TestCase
{
    #[DataProvider('provideValidHexCodes')]
    #[Test]
    public function canBeCreatedWithValidHexCode(string $value): void
    {
        $hexCode = new HexCode($value);

        self::assertSame($value, $hexCode->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideValidHexCodes(): iterable
    {
        yield 'six characters without hash' => ['CCCCCC'];
        yield 'six characters with hash' => ['#CCCCCC'];
        yield 'six characters lowercase' => ['cccccc'];
        yield 'six characters mixed case' => ['CcCcCc'];
        yield 'three characters without hash' => ['FFF'];
        yield 'three characters with hash' => ['#FFF'];
        yield 'three characters lowercase' => ['fff'];
        yield 'actual color red' => ['FF0000'];
        yield 'actual color green' => ['00FF00'];
        yield 'actual color blue' => ['0000FF'];
    }

    #[DataProvider('provideInvalidHexCodes')]
    #[Test]
    public function throwsExceptionForInvalidHexCode(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new HexCode($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidHexCodes(): iterable
    {
        yield 'empty string' => [''];
        yield 'only whitespace' => ['   '];
        yield 'invalid characters' => ['GGGGGG'];
        yield 'too short' => ['FF'];
        yield 'four characters' => ['FFFF'];
        yield 'five characters' => ['FFFFF'];
        yield 'too long' => ['FFFFFFF'];
        yield 'with spaces' => ['FF FF FF'];
    }

    #[DataProvider('provideToStringCases')]
    #[Test]
    public function toStringReturnsValueWithoutHash(string $value, string $expected): void
    {
        $hexCode = new HexCode($value);

        self::assertSame($expected, $hexCode->toString());
        self::assertSame($expected, (string) $hexCode);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideToStringCases(): iterable
    {
        yield 'without hash' => ['CCCCCC', 'CCCCCC'];
        yield 'with hash' => ['#CCCCCC', 'CCCCCC'];
        yield 'three chars without hash' => ['FFF', 'FFF'];
        yield 'three chars with hash' => ['#FFF', 'FFF'];
    }
}
