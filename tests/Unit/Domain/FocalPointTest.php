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
use Storyblok\ImageService\Domain\FocalPoint;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(FocalPoint::class)]
final class FocalPointTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithValidValues(): void
    {
        $focalPoint = new FocalPoint(719, 153, 720, 154);

        self::assertSame(719, $focalPoint->x1);
        self::assertSame(153, $focalPoint->y1);
        self::assertSame(720, $focalPoint->x2);
        self::assertSame(154, $focalPoint->y2);
    }

    #[Test]
    public function canBeCreatedFromString(): void
    {
        $focalPoint = FocalPoint::fromString('719x153:720x154');

        self::assertSame(719, $focalPoint->x1);
        self::assertSame(153, $focalPoint->y1);
        self::assertSame(720, $focalPoint->x2);
        self::assertSame(154, $focalPoint->y2);
    }

    #[Test]
    public function canBeCreatedWithZeroValues(): void
    {
        $focalPoint = new FocalPoint(0, 0, 0, 0);

        self::assertSame(0, $focalPoint->x1);
        self::assertSame(0, $focalPoint->y1);
        self::assertSame(0, $focalPoint->x2);
        self::assertSame(0, $focalPoint->y2);
    }

    #[Test]
    public function canBeCreatedWithSameStartAndEndPoints(): void
    {
        $focalPoint = new FocalPoint(100, 100, 100, 100);

        self::assertSame(100, $focalPoint->x1);
        self::assertSame(100, $focalPoint->y1);
        self::assertSame(100, $focalPoint->x2);
        self::assertSame(100, $focalPoint->y2);
    }

    #[Test]
    public function throwsExceptionWhenX1IsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FocalPoint(-1, 0, 100, 100);
    }

    #[Test]
    public function throwsExceptionWhenY1IsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FocalPoint(0, -1, 100, 100);
    }

    #[Test]
    public function throwsExceptionWhenX2IsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FocalPoint(0, 0, -1, 100);
    }

    #[Test]
    public function throwsExceptionWhenY2IsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FocalPoint(0, 0, 100, -1);
    }

    #[Test]
    public function throwsExceptionWhenX2IsLessThanX1(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FocalPoint(100, 0, 99, 100);
    }

    #[Test]
    public function throwsExceptionWhenY2IsLessThanY1(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new FocalPoint(0, 100, 100, 99);
    }

    #[DataProvider('provideInvalidStrings')]
    #[Test]
    public function throwsExceptionForInvalidStringFormat(string $value): void
    {
        $this->expectException(\InvalidArgumentException::class);

        FocalPoint::fromString($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidStrings(): iterable
    {
        yield 'empty string' => [''];
        yield 'missing colon' => ['719x153720x154'];
        yield 'missing x separator' => ['719153:720154'];
        yield 'with spaces' => ['719 x 153 : 720 x 154'];
        yield 'with negative values' => ['-1x153:720x154'];
        yield 'with letters' => ['axb:cxd'];
        yield 'with decimal values' => ['1.5x2.5:3.5x4.5'];
        yield 'single point' => ['719x153'];
        yield 'three points' => ['719x153:720x154:721x155'];
    }

    #[DataProvider('provideToStringCases')]
    #[Test]
    public function toStringReturnsExpectedFormat(int $x1, int $y1, int $x2, int $y2, string $expected): void
    {
        $focalPoint = new FocalPoint($x1, $y1, $x2, $y2);

        self::assertSame($expected, $focalPoint->toString());
        self::assertSame($expected, (string) $focalPoint);
    }

    /**
     * @return iterable<string, array{int, int, int, int, string}>
     */
    public static function provideToStringCases(): iterable
    {
        yield 'example from docs' => [719, 153, 720, 154, '719x153:720x154'];
        yield 'zero values' => [0, 0, 0, 0, '0x0:0x0'];
        yield 'large values' => [1920, 1080, 3840, 2160, '1920x1080:3840x2160'];
        yield 'same points' => [500, 500, 500, 500, '500x500:500x500'];
    }

    #[Test]
    public function fromStringAndToStringAreSymmetric(): void
    {
        $original = '719x153:720x154';
        $focalPoint = FocalPoint::fromString($original);

        self::assertSame($original, $focalPoint->toString());
    }
}
