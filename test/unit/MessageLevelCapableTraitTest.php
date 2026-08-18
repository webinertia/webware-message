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
use Webware\Message\MessageLevel;
use Webware\Message\MessageLevelCapableInterface;
use Webware\Message\MessageLevelCapableTrait;

#[CoversTrait(MessageLevelCapableTrait::class)]
final class MessageLevelCapableTraitTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getMessageLevelDefaultsToInfo(): void
    {
        $fixture = new class implements MessageLevelCapableInterface {
            use MessageLevelCapableTrait;
        };

        self::assertSame(MessageLevel::Info, $fixture->getMessageLevel());
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function setMessageLevelStoresAndReturnsFluentSelf(): void
    {
        $fixture = new class implements MessageLevelCapableInterface {
            use MessageLevelCapableTrait;
        };

        $result = $fixture->setMessageLevel(MessageLevel::Danger);

        self::assertSame($fixture, $result);
        self::assertSame(MessageLevel::Danger, $fixture->getMessageLevel());
    }
}
