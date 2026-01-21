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
use function Safe\preg_match;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
final readonly class FocalPoint implements \Stringable
{
    public function __construct(
        public int $x1,
        public int $y1,
        public int $x2,
        public int $y2,
    ) {
        Assert::greaterThanEq($x1, 0, 'x1 must be greater than or equal to 0, "%d" given.');
        Assert::greaterThanEq($y1, 0, 'y1 must be greater than or equal to 0, "%d" given.');
        Assert::greaterThanEq($x2, 0, 'x2 must be greater than or equal to 0, "%d" given.');
        Assert::greaterThanEq($y2, 0, 'y2 must be greater than or equal to 0, "%d" given.');
        Assert::greaterThanEq($x2, $x1, 'x2 must be greater than or equal to x1, x1="%d", x2="%d" given.');
        Assert::greaterThanEq($y2, $y1, 'y2 must be greater than or equal to y1, y1="%d", y2="%d" given.');
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public static function fromString(string $value): self
    {
        Assert::regex(
            $value,
            '/^(\d+)x(\d+):(\d+)x(\d+)$/',
            'Focal point must be in format "x1xy1:x2xy2" (e.g., "719x153:720x154"), "%s" given.',
        );

        preg_match('/^(\d+)x(\d+):(\d+)x(\d+)$/', $value, $matches);

        return new self(
            (int) $matches[1],
            (int) $matches[2],
            (int) $matches[3],
            (int) $matches[4],
        );
    }

    public function toString(): string
    {
        return \sprintf('%dx%d:%dx%d', $this->x1, $this->y1, $this->x2, $this->y2);
    }
}
