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

use OskarStark\Value\TrimmedNonEmptyString;
use Webmozart\Assert\Assert;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
final readonly class HexCode implements \Stringable
{
    public function __construct(
        public string $value,
    ) {
        $value = TrimmedNonEmptyString::fromString($value)->toString();

        Assert::regex(
            $value,
            '/^#?([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/',
            'Hex code must be a valid hexadecimal color code, "%s" given.',
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return ltrim($this->value, '#');
    }
}
