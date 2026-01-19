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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Storyblok\ImageService\Domain\Transparent;

/**
 * @author Silas Joisten <silasjoisten@proton.me>
 */
#[CoversClass(Transparent::class)]
final class TransparentTest extends TestCase
{
    #[Test]
    public function canBeCreated(): void
    {
        $transparent = new Transparent();

        self::assertInstanceOf(Transparent::class, $transparent);
    }

    #[Test]
    public function toStringReturnsTransparent(): void
    {
        $transparent = new Transparent();

        self::assertSame('transparent', $transparent->toString());
    }

    #[Test]
    public function implementsStringable(): void
    {
        $transparent = new Transparent();

        self::assertSame('transparent', (string) $transparent);
    }
}
