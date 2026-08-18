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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\MessageLevel;

#[CoversClass(MessageLevel::class)]
final class MessageLevelTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function levelValuesMatchSessionKeys(): void
    {
        self::assertSame('success', MessageLevel::Success->value);
        self::assertSame('danger', MessageLevel::Danger->value);
        self::assertSame('warning', MessageLevel::Warning->value);
        self::assertSame('info', MessageLevel::Info->value);
        self::assertSame('message', MessageLevel::Message->value);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function levelCanBeResolvedFromValue(): void
    {
        self::assertSame(MessageLevel::Success, MessageLevel::tryFrom('success'));
        self::assertSame(MessageLevel::Message, MessageLevel::tryFrom('message'));
        self::assertNull(MessageLevel::tryFrom('unknown'));
    }
}
