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

use Storyblok\ImageService\Domain\Angle;
use Storyblok\ImageService\Domain\Blur;
use Storyblok\ImageService\Domain\Brightness;
use Storyblok\ImageService\Domain\FocalPoint;
use Storyblok\ImageService\Domain\Format;
use Storyblok\ImageService\Domain\HexCode;
use Storyblok\ImageService\Domain\Quality;
use Storyblok\ImageService\Domain\RoundedCorner;
use Storyblok\ImageService\Domain\Transparent;
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
    public function blur(Blur $blur): self
    {
        $image = clone $this;

        if ('' !== $blur->toString()) {
            $image->filters['blur'] = $blur->toString();
        }

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/quality
     */
    public function quality(Quality $quality): self
    {
        $image = clone $this;
        $image->filters['quality'] = $quality->toString();

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/brightness
     */
    public function brightness(Brightness $brightness): self
    {
        $image = clone $this;
        $image->filters['brightness'] = $brightness->toString();

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
        Assert::range($width, 0, $this->originalWidth, 'Width must be between 0 and %d, "%d" given.');
        Assert::range($height, 0, $this->originalHeight, 'Height must be between 0 and %d, "%d" given.');

        $image = clone $this;

        $image->fitIn = true;
        $image->width = $width;
        $image->height = $height;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/fit-in
     */
    public function fill(HexCode|Transparent $color): self
    {
        $image = clone $this;
        $image->filters['fill'] = $color->toString();

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/format
     */
    public function format(Format $format): self
    {
        $image = clone $this;
        $image->filters['format'] = $format->value;
        $image->extension = $format->value;

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
    public function focalPoint(FocalPoint $focalPoint): self
    {
        $image = clone $this;
        $image->filters['focal'] = $focalPoint->toString();

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
    public function rotate(Angle $angle): self
    {
        $image = clone $this;
        $image->filters['rotate'] = $angle->value;

        return $image;
    }

    /**
     * @see https://www.storyblok.com/docs/api/image-service/operations/rounded-corners
     */
    public function roundedCorners(RoundedCorner $roundedCorner): self
    {
        $image = clone $this;
        $image->filters['round_corner'] = $roundedCorner->toString();

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
