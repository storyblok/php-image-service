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

namespace Storyblok\ImageService;

use Webmozart\Assert\Assert;
use function Safe\preg_match;

/**
 * @experimental
 *
 * @see https://www.storyblok.com/docs/api/image-service
 *
 * @author Silas Joisten <silasjoisten@proton.me>
 */
final class Image implements \Stringable
{
    private const array VALID_FORMATS = ['webp', 'jpeg', 'png', 'avif'];
    private const array VALID_ANGLES = [0, 90, 180, 270];
    private int $originalWidth;
    private int $originalHeight;
    private ?int $width = null;
    private ?int $height = null;
    private ?bool $fitIn = null;
    private ?string $crop = null;
    private bool $flipX = false;
    private bool $flipY = false;
    private string $extension;
    private string $name;

    /**
     * @var array<string, int|string>
     */
    private array $filters = [];

    public function __construct(
        private string $url,
    ) {
        // Ex: https://a.storyblok.com/f/287488/1400x900/2fc896c892/symfony-online-icon.svg
        //  --> 1400 x 900
        if (0 === preg_match('#/(\d+)x(\d+)/#', $url, $matches)) {
            throw new \InvalidArgumentException(\sprintf('Unable to extract dimensions from URL "%s".', $url));
        }

        $this->extension = \pathinfo($url, \PATHINFO_EXTENSION);
        $this->name = \pathinfo($url, \PATHINFO_FILENAME);

        [$this->originalWidth, $this->originalHeight] = [(int) $matches[1], (int) $matches[2]];
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/blur
     */
    public function blur(int $radius, int $sigma = 0): self
    {
        Assert::range($radius, 0, 150, 'The blur radius must be between 0 and 150. Got: %s');

        if (0 === $radius && 0 < $sigma) {
            throw new \InvalidArgumentException('The blur sigma cannot be set when the radius is 0.');
        }

        Assert::range($sigma, 0, 150, 'The blur sigma must be between 0 and 150. Got: %s');

        $image = clone $this;

        if (0 !== $radius) {
            $blur = (string) $radius;

            if (0 !== $sigma) {
                $blur .= \sprintf(', %d', $sigma);
            }

            $image->filters['blur'] = $blur;
        }

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/quality
     */
    public function quality(int $quality): self
    {
        Assert::range($quality, 0, 100, 'Quality must be between 0 and 100, "%d" given.');

        $image = clone $this;
        $image->filters['quality'] = (string) $quality;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/brightness
     */
    public function brightness(int $brightness): self
    {
        Assert::range($brightness, -100, 100, 'Brightness must be between -100 and 100, "%d" given.');

        $image = clone $this;
        $image->filters['brightness'] = (string) $brightness;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/crop
     */
    public function crop(int $left = 0, int $top = 0, ?int $right = null, ?int $bottom = null): self
    {
        $right ??= $this->originalWidth;
        $bottom ??= $this->originalHeight;

        $image = clone $this;

        if (0 === $left && 0 === $top && $right === $this->originalWidth && $bottom === $this->originalHeight) {
            $image->crop = null;

            return $image;
        }

        $image->crop = \sprintf('%dx%d:%dx%d', $left, $top, $right, $bottom);

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/fit-in
     */
    public function fitIn(int $width, int $height): self
    {
        Assert::greaterThanEq($width, 0, 'Width must be minimum 0, "%d" given.');
        Assert::greaterThanEq($height, 0, 'Height must be minimum 0, "%d" given.');

        $image = clone $this;

        $image->fitIn = true;
        $image->width = $width;
        $image->height = $height;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/fit-in
     */
    public function fill(string $color): self
    {
        $color = trim($color);

        if ('transparent' !== $color) {
            Assert::regex(
                $color,
                '/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
                'Color must be "transparent" or a valid hexadecimal color code, "%s" given.',
            );
            $color = ltrim($color, '#');
        }

        $image = clone $this;
        $image->filters['fill'] = $color;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/format
     */
    public function format(string $format): self
    {
        Assert::inArray($format, self::VALID_FORMATS, 'Format must be one of "webp", "jpeg", "png", "avif", "%s" given.');

        $image = clone $this;
        $image->filters['format'] = $format;
        $image->extension = $format;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/flip
     */
    public function flipX(): self
    {
        $image = clone $this;
        $image->flipX = true;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/flip
     */
    public function flipY(): self
    {
        $image = clone $this;
        $image->flipY = true;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/focal-point
     */
    public function focalPoint(string $focalPoint): self
    {
        Assert::regex(
            $focalPoint,
            '/^(\d+)x(\d+):(\d+)x(\d+)$/',
            'Focal point must be in format "x1xy1:x2xy2" (e.g., "719x153:720x154"), "%s" given.',
        );

        preg_match('/^(\d+)x(\d+):(\d+)x(\d+)$/', $focalPoint, $matches);

        $x1 = (int) $matches[1];
        $y1 = (int) $matches[2];
        $x2 = (int) $matches[3];
        $y2 = (int) $matches[4];

        Assert::greaterThanEq($x2, $x1, 'x2 must be greater than or equal to x1, x1="%d", x2="%d" given.');
        Assert::greaterThanEq($y2, $y1, 'y2 must be greater than or equal to y1, y1="%d", y2="%d" given.');

        $image = clone $this;
        $image->filters['focal'] = $focalPoint;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/grayscale
     */
    public function grayscale(): self
    {
        $image = clone $this;
        $image->filters['grayscale'] = '';

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/resize
     */
    public function resize(int $width = 0, int $height = 0): self
    {
        $image = clone $this;

        if (0 === $width) {
            Assert::greaterThan($height, 0);
            $width = $this->originalWidth;
            $height = $this->originalHeight * $width / $this->originalWidth;
        }

        if (0 === $height) {
            Assert::greaterThan($width, 0);
            $height = $this->originalHeight;
            $width = $this->originalWidth * $height / $this->originalHeight;
        }

        $image->width = (int) $width;
        $image->height = (int) $height;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/resize
     */
    public function noUpscale(): self
    {
        $image = clone $this;
        $image->filters['no_upscale'] = '';

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/rotate
     */
    public function rotate(int $angle): self
    {
        Assert::inArray($angle, self::VALID_ANGLES, 'Angle must be one of 0, 90, 180, 270, "%d" given.');

        $image = clone $this;
        $image->filters['rotate'] = $angle;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/rounded-corners
     */
    public function roundedCorners(
        int $radius,
        ?int $ellipsis = null,
        int $red = 255,
        int $green = 255,
        int $blue = 255,
        bool $transparent = false,
    ): self {
        Assert::greaterThanEq($radius, 0, 'Radius must be greater than or equal to 0, "%d" given.');

        if (null !== $ellipsis) {
            Assert::greaterThanEq($ellipsis, 0, 'Ellipsis must be greater than or equal to 0, "%d" given.');
        }

        Assert::range($red, 0, 255, 'Red must be between 0 and 255, "%d" given.');
        Assert::range($green, 0, 255, 'Green must be between 0 and 255, "%d" given.');
        Assert::range($blue, 0, 255, 'Blue must be between 0 and 255, "%d" given.');

        $radiusPart = null !== $ellipsis
            ? \sprintf('%d|%d', $radius, $ellipsis)
            : (string) $radius;

        $image = clone $this;
        $image->filters['round_corner'] = \sprintf(
            '%s,%d,%d,%d,%d',
            $radiusPart,
            $red,
            $green,
            $blue,
            $transparent ? 1 : 0,
        );

        return $image;
    }

    public function getWidth(): int
    {
        return $this->width ?? $this->originalWidth;
    }

    public function getHeight(): int
    {
        return $this->height ?? $this->originalHeight;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function toString(): string
    {
        $url = $this->url;

        // if nothing to transform or resize, return the original URL.
        if (null === $this->width
            && null === $this->height
            && null === $this->fitIn
            && null === $this->crop
            && [] === $this->filters
            && !$this->flipX
            && !$this->flipY
        ) {
            return $url;
        }

        $url .= '/m';

        if (null !== $this->crop) {
            $url .= '/'.$this->crop;
        }

        if (null !== $this->width || null !== $this->height || $this->flipX || $this->flipY) {
            if (true === $this->fitIn) {
                $url .= '/fit-in';
            }

            $flipXPrefix = $this->flipX ? '-' : '';
            $flipYPrefix = $this->flipY ? '-' : '';

            $url .= \sprintf('/%s%dx%s%d', $flipXPrefix, $this->getWidth(), $flipYPrefix, $this->getHeight());
        }

        if ([] !== $filters = $this->filters) {
            $url .= '/filters';
            ksort($filters);

            foreach ($filters as $name => $value) {
                $url .= \sprintf(':%s(%s)', $name, $value);
            }
        }

        return $url;
    }
}
