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
use Storyblok\ImageService\Domain\Brightness;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(Brightness::class)]
final class BrightnessTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithValidValue(): void
    {
        $brightness = new Brightness(50);

        self::assertSame(50, $brightness->value);
    }

    #[Test]
    public function canBeCreatedWithZero(): void
    {
        $brightness = new Brightness(0);

        self::assertSame(0, $brightness->value);
    }

    #[Test]
    public function canBeCreatedWithNegativeValue(): void
    {
        $brightness = new Brightness(-50);

        self::assertSame(-50, $brightness->value);
    }

    #[Test]
    public function canBeCreatedWithMinValue(): void
    {
        $brightness = new Brightness(-100);

        self::assertSame(-100, $brightness->value);
    }

    #[Test]
    public function canBeCreatedWithMaxValue(): void
    {
        $brightness = new Brightness(100);

        self::assertSame(100, $brightness->value);
    }

    #[Test]
    public function throwsExceptionWhenValueIsBelowMin(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Brightness(-101);
    }

    #[Test]
    public function throwsExceptionWhenValueExceedsMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Brightness(101);
    }

    #[DataProvider('provideToStringCases')]
    #[Test]
    public function toStringReturnsExpectedFormat(int $value, string $expected): void
    {
        $brightness = new Brightness($value);

        self::assertSame($expected, $brightness->toString());
        self::assertSame($expected, (string) $brightness);
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function provideToStringCases(): iterable
    {
        yield 'zero' => [0, '0'];
        yield 'positive' => [50, '50'];
        yield 'negative' => [-50, '-50'];
        yield 'max' => [100, '100'];
        yield 'min' => [-100, '-100'];
    }
}
