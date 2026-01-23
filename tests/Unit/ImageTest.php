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

namespace Storyblok\ImageService\Tests\Unit;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Storyblok\ImageService\Image;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(Image::class)]
final class ImageTest extends TestCase
{
    private const string URL = 'https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg';

    #[Test]
    public function returnsOriginalUrlWhenNoTransformations(): void
    {
        $image = new Image(self::URL);

        self::assertSame(self::URL, $image->toString());
        self::assertSame(self::URL, (string) $image);
    }

    #[Test]
    public function throwsExceptionForInvalidUrl(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Image('https://example.com/image.jpg');
    }

    #[Test]
    public function extractsDimensionsFromUrl(): void
    {
        $image = new Image(self::URL);

        self::assertSame(1400, $image->getWidth());
        self::assertSame(900, $image->getHeight());
    }

    #[DataProvider('provideBlurCases')]
    #[Test]
    public function blur(int $radius, int $sigma, string $expected): void
    {
        $image = (new Image(self::URL))->blur($radius, $sigma);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, int, string}>
     */
    public static function provideBlurCases(): iterable
    {
        yield 'no blur' => [0, 0, self::URL];
        yield 'radius only' => [10, 0, self::URL.'/m/filters:blur(10)'];
        yield 'radius and sigma' => [10, 5, self::URL.'/m/filters:blur(10, 5)'];
    }

    #[DataProvider('provideQualityCases')]
    #[Test]
    public function quality(int $value, string $expected): void
    {
        $image = (new Image(self::URL))->quality($value);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function provideQualityCases(): iterable
    {
        yield 'low quality' => [25, self::URL.'/m/filters:quality(25)'];
        yield 'default quality' => [80, self::URL.'/m/filters:quality(80)'];
        yield 'max quality' => [100, self::URL.'/m/filters:quality(100)'];
    }

    #[DataProvider('provideBrightnessCases')]
    #[Test]
    public function brightness(int $value, string $expected): void
    {
        $image = (new Image(self::URL))->brightness($value);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function provideBrightnessCases(): iterable
    {
        yield 'zero' => [0, self::URL.'/m/filters:brightness(0)'];
        yield 'positive' => [50, self::URL.'/m/filters:brightness(50)'];
        yield 'negative' => [-50, self::URL.'/m/filters:brightness(-50)'];
    }

    #[DataProvider('provideFormatCases')]
    #[Test]
    public function format(string $format, string $expected): void
    {
        $image = (new Image(self::URL))->format($format);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideFormatCases(): iterable
    {
        yield 'webp' => ['webp', self::URL.'/m/filters:format(webp)'];
        yield 'jpeg' => ['jpeg', self::URL.'/m/filters:format(jpeg)'];
        yield 'png' => ['png', self::URL.'/m/filters:format(png)'];
        yield 'avif' => ['avif', self::URL.'/m/filters:format(avif)'];
    }

    #[DataProvider('provideRotateCases')]
    #[Test]
    public function rotate(int $angle, string $expected): void
    {
        $image = (new Image(self::URL))->rotate($angle);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function provideRotateCases(): iterable
    {
        yield '0 degrees' => [0, self::URL.'/m/filters:rotate(0)'];
        yield '90 degrees' => [90, self::URL.'/m/filters:rotate(90)'];
        yield '180 degrees' => [180, self::URL.'/m/filters:rotate(180)'];
        yield '270 degrees' => [270, self::URL.'/m/filters:rotate(270)'];
    }

    #[Test]
    public function grayscale(): void
    {
        $image = (new Image(self::URL))->grayscale();

        self::assertSame(self::URL.'/m/filters:grayscale()', $image->toString());
    }

    #[Test]
    public function noUpscale(): void
    {
        $image = (new Image(self::URL))->noUpscale();

        self::assertSame(self::URL.'/m/filters:no_upscale()', $image->toString());
    }

    #[DataProvider('provideFillCases')]
    #[Test]
    public function fill(string $color, string $expected): void
    {
        $image = (new Image(self::URL))->fill($color);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideFillCases(): iterable
    {
        yield 'transparent' => ['transparent', self::URL.'/m/filters:fill(transparent)'];
        yield 'hex without hash' => ['CCCCCC', self::URL.'/m/filters:fill(CCCCCC)'];
        yield 'hex with hash' => ['#FF0000', self::URL.'/m/filters:fill(FF0000)'];
    }

    #[DataProvider('provideRoundedCornersCases')]
    #[Test]
    public function roundedCorners(int $radius, ?int $ellipsis, int $red, int $green, int $blue, bool $transparent, string $expected): void
    {
        $image = (new Image(self::URL))->roundedCorners($radius, $ellipsis, $red, $green, $blue, $transparent);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, null|int, int, int, int, bool, string}>
     */
    public static function provideRoundedCornersCases(): iterable
    {
        yield 'radius only' => [20, null, 255, 255, 255, false, self::URL.'/m/filters:round_corner(20,255,255,255,0)'];
        yield 'with ellipsis' => [20, 10, 255, 255, 255, false, self::URL.'/m/filters:round_corner(20|10,255,255,255,0)'];
        yield 'with colors' => [20, null, 128, 64, 32, false, self::URL.'/m/filters:round_corner(20,128,64,32,0)'];
        yield 'transparent' => [20, null, 255, 255, 255, true, self::URL.'/m/filters:round_corner(20,255,255,255,1)'];
    }

    #[DataProvider('provideResizeCases')]
    #[Test]
    public function resize(int $width, int $height, string $expected): void
    {
        $image = (new Image(self::URL))->resize($width, $height);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, int, string}>
     */
    public static function provideResizeCases(): iterable
    {
        yield 'both dimensions' => [700, 450, self::URL.'/m/700x450'];
        yield 'width only keeps original dimensions' => [700, 0, self::URL.'/m/1400x900'];
        yield 'height only keeps original dimensions' => [0, 450, self::URL.'/m/1400x900'];
    }

    #[DataProvider('provideFitInCases')]
    #[Test]
    public function fitIn(int $width, int $height, string $expected): void
    {
        $image = (new Image(self::URL))->fitIn($width, $height);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, int, string}>
     */
    public static function provideFitInCases(): iterable
    {
        yield 'smaller than original' => [700, 450, self::URL.'/m/fit-in/700x450'];
        yield 'larger than original' => [2000, 1500, self::URL.'/m/fit-in/2000x1500'];
        yield 'same as original' => [1400, 900, self::URL.'/m/fit-in/1400x900'];
    }

    #[DataProvider('provideCropCases')]
    #[Test]
    public function crop(int $left, int $top, ?int $right, ?int $bottom, string $expected): void
    {
        $image = (new Image(self::URL))->crop($left, $top, $right, $bottom);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{int, int, null|int, null|int, string}>
     */
    public static function provideCropCases(): iterable
    {
        yield 'full image returns original' => [0, 0, 1400, 900, self::URL];
        yield 'defaults to full image' => [0, 0, null, null, self::URL];
        yield 'custom crop' => [100, 50, 800, 600, self::URL.'/m/100x50:800x600'];
        yield 'from origin' => [0, 0, 700, 450, self::URL.'/m/0x0:700x450'];
    }

    #[DataProvider('provideFocalPointCases')]
    #[Test]
    public function focalPoint(string $focalPoint, string $expected): void
    {
        $image = (new Image(self::URL))->focalPoint($focalPoint);

        self::assertSame($expected, $image->toString());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideFocalPointCases(): iterable
    {
        yield 'center focal point' => ['500x300:900x600', self::URL.'/m/filters:focal(500x300:900x600)'];
        yield 'corner focal point' => ['0x0:200x200', self::URL.'/m/filters:focal(0x0:200x200)'];
        yield 'from string' => ['719x153:720x154', self::URL.'/m/filters:focal(719x153:720x154)'];
    }

    #[Test]
    public function flipX(): void
    {
        $image = (new Image(self::URL))->flipX();

        self::assertSame(self::URL.'/m/-1400x900', $image->toString());
    }

    #[Test]
    public function flipY(): void
    {
        $image = (new Image(self::URL))->flipY();

        self::assertSame(self::URL.'/m/1400x-900', $image->toString());
    }

    #[Test]
    public function flipXAndY(): void
    {
        $image = (new Image(self::URL))->flipX()->flipY();

        self::assertSame(self::URL.'/m/-1400x-900', $image->toString());
    }

    #[Test]
    public function multipleFiltersAreSortedAlphabetically(): void
    {
        $image = (new Image(self::URL))
            ->quality(80)
            ->brightness(10)
            ->format('webp');

        self::assertSame(
            self::URL.'/m/filters:brightness(10):format(webp):quality(80)',
            $image->toString(),
        );
    }

    #[Test]
    public function resizeWithFilters(): void
    {
        $image = (new Image(self::URL))
            ->resize(700, 450)
            ->quality(80)
            ->format('webp');

        self::assertSame(
            self::URL.'/m/700x450/filters:format(webp):quality(80)',
            $image->toString(),
        );
    }

    #[Test]
    public function fitInWithFilters(): void
    {
        $image = (new Image(self::URL))
            ->fitIn(700, 450)
            ->fill('CCCCCC')
            ->format('webp');

        self::assertSame(
            self::URL.'/m/fit-in/700x450/filters:fill(CCCCCC):format(webp)',
            $image->toString(),
        );
    }

    #[Test]
    public function cropWithResize(): void
    {
        $image = (new Image(self::URL))
            ->crop(100, 50, 800, 600)
            ->resize(350, 275);

        self::assertSame(
            self::URL.'/m/100x50:800x600/350x275',
            $image->toString(),
        );
    }

    #[Test]
    public function flipWithResize(): void
    {
        $image = (new Image(self::URL))
            ->resize(700, 450)
            ->flipX();

        self::assertSame(
            self::URL.'/m/-700x450',
            $image->toString(),
        );
    }

    #[Test]
    public function allTransformationsCombined(): void
    {
        $image = (new Image(self::URL))
            ->crop(100, 50, 800, 600)
            ->resize(350, 275)
            ->flipX()
            ->quality(80)
            ->format('webp')
            ->grayscale();

        self::assertSame(
            self::URL.'/m/100x50:800x600/-350x275/filters:format(webp):grayscale():quality(80)',
            $image->toString(),
        );
    }

    #[Test]
    public function immutability(): void
    {
        $original = new Image(self::URL);
        $resized = $original->resize(700, 450);
        $withQuality = $resized->quality(80);

        self::assertSame(self::URL, $original->toString());
        self::assertSame(self::URL.'/m/700x450', $resized->toString());
        self::assertSame(self::URL.'/m/700x450/filters:quality(80)', $withQuality->toString());
    }

    #[DataProvider('provideExtensionCases')]
    #[Test]
    public function extractsExtensionFromUrl(string $url, string $expectedExtension): void
    {
        $image = new Image($url);

        self::assertSame($expectedExtension, $image->getExtension());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideExtensionCases(): iterable
    {
        yield 'jpg' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg', 'jpg'];
        yield 'jpeg' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpeg', 'jpeg'];
        yield 'png' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.png', 'png'];
        yield 'webp' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.webp', 'webp'];
        yield 'svg' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.svg', 'svg'];
        yield 'gif' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.gif', 'gif'];
        yield 'avif' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.avif', 'avif'];
    }

    #[DataProvider('provideFormatExtensionCases')]
    #[Test]
    public function formatChangesExtension(string $format, string $expectedExtension): void
    {
        $image = (new Image(self::URL))->format($format);

        self::assertSame($expectedExtension, $image->getExtension());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideFormatExtensionCases(): iterable
    {
        yield 'webp' => ['webp', 'webp'];
        yield 'jpeg' => ['jpeg', 'jpeg'];
        yield 'png' => ['png', 'png'];
        yield 'avif' => ['avif', 'avif'];
    }

    #[Test]
    public function formatDoesNotChangeOriginalExtension(): void
    {
        $original = new Image(self::URL);
        $formatted = $original->format('webp');

        self::assertSame('jpg', $original->getExtension());
        self::assertSame('webp', $formatted->getExtension());
    }

    #[DataProvider('provideNameCases')]
    #[Test]
    public function extractsNameFromUrl(string $url, string $expectedName): void
    {
        $image = new Image($url);

        self::assertSame($expectedName, $image->getName());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideNameCases(): iterable
    {
        yield 'simple name' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/image.jpg', 'image'];
        yield 'name with hyphens' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/my-image-file.jpg', 'my-image-file'];
        yield 'name with underscores' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/my_image_file.png', 'my_image_file'];
        yield 'complex name' => ['https://a.storyblok.com/f/287488/1400x900/2fc896c892/symfony-online-icon.svg', 'symfony-online-icon'];
    }
}
