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

namespace Storyblok\ImageService\Domain;

use Webmozart\Assert\Assert;

/**
 * @see https://www.storyblok.com/docs/api/image-service/operations/rounded-corners
 *
 * @author Silas Joisten <silasjoisten@proton.me>
 */
final readonly class RoundedCorner implements \Stringable
{
    public function __construct(
        public int $radius,
        public ?int $ellipsis = null,
        public int $red = 255,
        public int $green = 255,
        public int $blue = 255,
        public bool $transparent = false,
    ) {
        Assert::greaterThanEq($radius, 0, 'Radius must be greater than or equal to 0, "%d" given.');

        if (null !== $ellipsis) {
            Assert::greaterThanEq($ellipsis, 0, 'Ellipsis must be greater than or equal to 0, "%d" given.');
        }

        Assert::range($red, 0, 255, 'Red must be between 0 and 255, "%d" given.');
        Assert::range($green, 0, 255, 'Green must be between 0 and 255, "%d" given.');
        Assert::range($blue, 0, 255, 'Blue must be between 0 and 255, "%d" given.');
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $radiusPart = null !== $this->ellipsis
            ? \sprintf('%d|%d', $this->radius, $this->ellipsis)
            : (string) $this->radius;

        return \sprintf(
            '%s,%d,%d,%d,%d',
            $radiusPart,
            $this->red,
            $this->green,
            $this->blue,
            $this->transparent ? 1 : 0,
        );
    }
}
