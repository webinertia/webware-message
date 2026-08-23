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
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;
use Webware\Message\MessageIcon;
use Webware\Message\MessageLevel;

#[CoversClass(MessageIcon::class)]
#[CoversMethod(MessageIcon::class, 'tryFromLevel')]
final class MessageIconTest extends TestCase
{
    /**
     * @return iterable<string, array{MessageLevel|string, MessageIcon}>
     */
    public static function levelProvider(): iterable
    {
        yield 'success enum' => [MessageLevel::Success, MessageIcon::Success];
        yield 'danger enum' => [MessageLevel::Danger, MessageIcon::Danger];
        yield 'warning enum' => [MessageLevel::Warning, MessageIcon::Warning];
        yield 'info enum' => [MessageLevel::Info, MessageIcon::Info];
        yield 'message enum' => [MessageLevel::Message, MessageIcon::Info];
        yield 'success string' => ['success', MessageIcon::Success];
        yield 'danger string' => ['danger', MessageIcon::Danger];
        yield 'warning string' => ['warning', MessageIcon::Warning];
        yield 'info string' => ['info', MessageIcon::Info];
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function iconsExposeBootstrapIconNames(): void
    {
        self::assertSame('check-circle', MessageIcon::Success->value);
        self::assertSame('exclamation-octagon', MessageIcon::Danger->value);
        self::assertSame('exclamation-triangle', MessageIcon::Warning->value);
        self::assertSame('info-circle', MessageIcon::Info->value);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    #[DataProvider('levelProvider')]
    public function tryFromLevelMapsEachLevelToItsIcon(MessageLevel|string $level, MessageIcon $expected): void
    {
        self::assertSame($expected, MessageIcon::tryFromLevel($level));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function tryFromLevelThrowsForUnknownStringLevel(): void
    {
        $this->expectException(UnexpectedValueException::class);

        MessageIcon::tryFromLevel('unknown');
    }
}
