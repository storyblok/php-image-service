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

use OskarStark\Enum\Test\EnumTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Storyblok\ImageService\Domain\Format;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(Format::class)]
final class FormatTest extends EnumTestCase
{
    protected static function getClass(): string
    {
        return Format::class;
    }

    protected static function getNumberOfValues(): int
    {
        return 4;
    }
}
