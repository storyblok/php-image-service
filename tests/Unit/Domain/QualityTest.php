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
use Storyblok\ImageService\Domain\Quality;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(Quality::class)]
final class QualityTest extends TestCase
{
    #[Test]
    public function canBeCreatedWithValidValue(): void
    {
        $quality = new Quality(80);

        self::assertSame(80, $quality->value);
    }

    #[Test]
    public function canBeCreatedWithMinValue(): void
    {
        $quality = new Quality(0);

        self::assertSame(0, $quality->value);
    }

    #[Test]
    public function canBeCreatedWithMaxValue(): void
    {
        $quality = new Quality(100);

        self::assertSame(100, $quality->value);
    }

    #[Test]
    public function throwsExceptionWhenValueIsNegative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Quality(-1);
    }

    #[Test]
    public function throwsExceptionWhenValueExceedsMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Quality(101);
    }

    #[DataProvider('provideToStringCases')]
    #[Test]
    public function toStringReturnsExpectedFormat(int $value, string $expected): void
    {
        $quality = new Quality($value);

        self::assertSame($expected, $quality->toString());
        self::assertSame($expected, (string) $quality);
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function provideToStringCases(): iterable
    {
        yield 'zero' => [0, '0'];
        yield 'default' => [80, '80'];
        yield 'max' => [100, '100'];
        yield 'low' => [25, '25'];
    }
}
