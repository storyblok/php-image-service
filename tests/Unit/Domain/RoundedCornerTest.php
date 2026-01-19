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
use Storyblok\ImageService\Domain\RoundedCorner;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(RoundedCorner::class)]
final class RoundedCornerTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithRadiusOnly(): void
    {
        $roundedCorner = new RoundedCorner(20);

        self::assertSame(20, $roundedCorner->radius);
        self::assertNull($roundedCorner->ellipsis);
        self::assertSame(255, $roundedCorner->red);
        self::assertSame(255, $roundedCorner->green);
        self::assertSame(255, $roundedCorner->blue);
        self::assertFalse($roundedCorner->transparent);
    }

    #[Test]
    public function canBeCreatedWithAllParameters(): void
    {
        $roundedCorner = new RoundedCorner(20, 10, 128, 64, 32, true);

        self::assertSame(20, $roundedCorner->radius);
        self::assertSame(10, $roundedCorner->ellipsis);
        self::assertSame(128, $roundedCorner->red);
        self::assertSame(64, $roundedCorner->green);
        self::assertSame(32, $roundedCorner->blue);
        self::assertTrue($roundedCorner->transparent);
    }

    #[Test]
    public function canBeCreatedWithZeroRadius(): void
    {
        $roundedCorner = new RoundedCorner(0);

        self::assertSame(0, $roundedCorner->radius);
    }

    #[Test]
    public function throwsExceptionWhenRadiusIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoundedCorner(-1);
    }

    #[Test]
    public function throwsExceptionWhenEllipsisIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoundedCorner(20, -1);
    }

    #[DataProvider('provideInvalidColorValues')]
    #[Test]
    public function throwsExceptionWhenColorValueIsInvalid(int $red, int $green, int $blue): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new RoundedCorner(20, null, $red, $green, $blue);
    }

    /**
     * @return iterable<string, array{int, int, int}>
     */
    public static function provideInvalidColorValues(): iterable
    {
        yield 'red negative' => [-1, 0, 0];
        yield 'red exceeds max' => [256, 0, 0];
        yield 'green negative' => [0, -1, 0];
        yield 'green exceeds max' => [0, 256, 0];
        yield 'blue negative' => [0, 0, -1];
        yield 'blue exceeds max' => [0, 0, 256];
    }

    #[DataProvider('provideToStringCases')]
    #[Test]
    public function toStringReturnsExpectedFormat(
        int $radius,
        ?int $ellipsis,
        int $red,
        int $green,
        int $blue,
        bool $transparent,
        string $expected,
    ): void {
        $roundedCorner = new RoundedCorner($radius, $ellipsis, $red, $green, $blue, $transparent);

        self::assertSame($expected, $roundedCorner->toString());
        self::assertSame($expected, (string) $roundedCorner);
    }

    /**
     * @return iterable<string, array{int, null|int, int, int, int, bool, string}>
     */
    public static function provideToStringCases(): iterable
    {
        yield 'radius only with defaults' => [20, null, 255, 255, 255, false, '20,255,255,255,0'];
        yield 'with ellipsis' => [20, 10, 255, 255, 255, false, '20|10,255,255,255,0'];
        yield 'with custom colors' => [20, null, 128, 64, 32, false, '20,128,64,32,0'];
        yield 'with transparent' => [20, null, 255, 255, 255, true, '20,255,255,255,1'];
        yield 'all custom values' => [50, 25, 100, 150, 200, true, '50|25,100,150,200,1'];
        yield 'zero radius' => [0, null, 0, 0, 0, false, '0,0,0,0,0'];
    }
}
