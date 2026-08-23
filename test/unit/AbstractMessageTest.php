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
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\AbstractMessage;
use Webware\Message\MessageInterface;

#[CoversClass(AbstractMessage::class)]
#[CoversMethod(AbstractMessage::class, '__construct')]
#[CoversMethod(AbstractMessage::class, 'getMessage')]
#[CoversMethod(AbstractMessage::class, 'jsonSerialize')]
#[CoversMethod(AbstractMessage::class, '__toString')]
final class AbstractMessageTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function messageImplementsPackageContract(): void
    {
        self::assertInstanceOf(MessageInterface::class, $this->message('value'));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function messageValueIsAccessibleThroughAllContractMethods(): void
    {
        $message = $this->message('Test message');

        self::assertSame('Test message', $message->getMessage());
        self::assertSame('Test message', $message->jsonSerialize());
        self::assertSame('Test message', (string) $message);
    }

    private function message(string $value): AbstractMessage
    {
        return new class($value) extends AbstractMessage {};
    }
}
