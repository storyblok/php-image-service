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
use Storyblok\ImageService\Domain\Blur;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(Blur::class)]
final class BlurTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithValidValues(): void
    {
        $blur = new Blur(10, 5);

        self::assertSame(10, $blur->radius);
        self::assertSame(5, $blur->sigma);
    }

    #[Test]
    public function canBeCreatedWithZeroRadiusAndZeroSigma(): void
    {
        $blur = new Blur(0, 0);

        self::assertSame(0, $blur->radius);
        self::assertSame(0, $blur->sigma);
    }

    #[Test]
    public function canBeCreatedWithMaxValues(): void
    {
        $blur = new Blur(150, 150);

        self::assertSame(150, $blur->radius);
        self::assertSame(150, $blur->sigma);
    }

    #[Test]
    public function throwsExceptionWhenRadiusIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Blur(-1, 0);
    }

    #[Test]
    public function throwsExceptionWhenRadiusExceedsMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Blur(151, 0);
    }

    #[Test]
    public function throwsExceptionWhenSigmaIsSetWithZeroRadius(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The blur sigma cannot be set when the radius is 0.');

        new Blur(0, 10);
    }

    #[Test]
    public function throwsExceptionWhenSigmaIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Blur(10, -1);
    }

    #[Test]
    public function throwsExceptionWhenSigmaExceedsMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Blur(10, 151);
    }

    #[DataProvider('provideToStringCases')]
    #[Test]
    public function toStringReturnsExpectedFormat(int $radius, int $sigma, string $expected): void
    {
        $blur = new Blur($radius, $sigma);

        self::assertSame($expected, $blur->toString());
        self::assertSame($expected, (string) $blur);
    }

    /**
     * @return iterable<string, array{int, int, string}>
     */
    public static function provideToStringCases(): iterable
    {
        yield 'zero radius and sigma' => [0, 0, ''];
        yield 'radius only' => [10, 0, '10'];
        yield 'radius and sigma' => [10, 5, '10, 5'];
        yield 'max values' => [150, 150, '150, 150'];
    }
}
