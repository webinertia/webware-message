<?php

declare(strict_types=1);

/**
 * This file is part of the Webware Webware Message package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WebwareTest\Message;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\MessageIcon;
use Webware\Message\MessageIconCapableInterface;
use Webware\Message\MessageIconCapableTrait;

#[CoversTrait(MessageIconCapableTrait::class)]
final class MessageIconCapableTraitTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getMessageIconDefaultsToNull(): void
    {
        $fixture = new class implements MessageIconCapableInterface {
            use MessageIconCapableTrait;
        };

        self::assertNull($fixture->getMessageIcon());
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function setMessageIconStoresAndReturnsFluentSelf(): void
    {
        $fixture = new class implements MessageIconCapableInterface {
            use MessageIconCapableTrait;
        };

        $result = $fixture->setMessageIcon(MessageIcon::Success);

        self::assertSame($fixture, $result);
        self::assertSame(MessageIcon::Success, $fixture->getMessageIcon());
    }
}
