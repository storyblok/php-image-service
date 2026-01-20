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
 * @author Silas Joisten <silasjoisten@proton.me>
 */
final readonly class Blur implements \Stringable
{
    public function __construct(
        public int $radius,
        public int $sigma = 0,
    ) {
        Assert::range($radius, 0, 150, 'The blur radius must be between 0 and 150. Got: %s');

        if (0 === $radius && 0 < $sigma) {
            throw new \InvalidArgumentException('The blur sigma cannot be set when the radius is 0.');
        }

        Assert::range($sigma, 0, 150, 'The blur sigma must be between 0 and 150. Got: %s');
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        $string = '';

        if (0 !== $this->radius) {
            $string .= \sprintf('%d', $this->radius);
        }

        if (0 !== $this->sigma) {
            $string .= \sprintf(', %d', $this->sigma);
        }

        return $string;
    }
}
