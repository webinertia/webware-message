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
use Webware\Message\SystemMessengerAwareInterface;
use Webware\Message\SystemMessengerAwareTrait;
use Webware\Message\SystemMessengerInterface;

#[CoversTrait(SystemMessengerAwareTrait::class)]
final class SystemMessengerAwareTraitTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function setSystemMessengerStoresAndReturnsFluentSelf(): void
    {
        $messenger = $this->createStub(SystemMessengerInterface::class);
        $fixture = new class($messenger) implements SystemMessengerAwareInterface {
            use SystemMessengerAwareTrait;

            public function __construct(SystemMessengerInterface $systemMessenger)
            {
                $this->systemMessenger = $systemMessenger;
            }
        };

        $result = $fixture->setSystemMessenger($messenger);

        self::assertSame($fixture, $result);
        self::assertSame($messenger, $fixture->getSystemMessenger());
    }
}
