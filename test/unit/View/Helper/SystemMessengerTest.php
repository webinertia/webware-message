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

namespace WebwareTest\Message\View\Helper;

use Mezzio\Session\SessionInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\SystemMessenger;
use Webware\Message\SystemMessengerInterface;
use Webware\Message\View\Helper\SystemMessenger as SystemMessengerHelper;

use function array_key_exists;
use function str_contains;

#[CoversClass(SystemMessengerHelper::class)]
#[CoversMethod(SystemMessengerHelper::class, 'getMessenger')]
#[CoversMethod(SystemMessengerHelper::class, 'setMessenger')]
#[CoversMethod(SystemMessengerHelper::class, '__invoke')]
final class SystemMessengerTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function helperStartsWithoutMessenger(): void
    {
        self::assertNull(new SystemMessengerHelper()->getMessenger());
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeRendersToastForEachStoredMessageLevel(): void
    {
        $helper = new SystemMessengerHelper();
        $helper->setMessenger($this->messengerWithMessages([
            'success' => [
                ['message' => 'Saved!', 'hops' => 1, 'key' => 'success', 'id' => null],
            ],
            'info'    => [
                ['message' => 'Hello', 'hops' => 1, 'key' => 'info', 'id' => null],
                ['message' => 'World', 'hops' => 1, 'key' => 'info', 'id' => null],
            ],
        ]));

        $html = $helper();

        self::assertTrue(str_contains($html, 'bg-success-subtle'));
        self::assertTrue(str_contains($html, 'bi-check-circle'));
        self::assertTrue(str_contains($html, 'Saved!'));
        self::assertTrue(str_contains($html, 'bg-info-subtle'));
        self::assertTrue(str_contains($html, 'bi-info-circle'));
        self::assertTrue(str_contains($html, 'Hello<br>World'));
        self::assertTrue(str_contains($html, 'role="alert"'));
        self::assertFalse(str_contains($html, 'bg-danger-subtle'));
        self::assertFalse(str_contains($html, 'bg-warning-subtle'));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeReturnsEmptyStringWhenNoMessengerIsSet(): void
    {
        self::assertSame('', (new SystemMessengerHelper())());
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function messageKeyConstantMatchesSessionKey(): void
    {
        self::assertSame(SystemMessengerInterface::SESSION_KEY, SystemMessengerHelper::MESSAGE_KEY);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function setMessengerStoresInstance(): void
    {
        $helper    = new SystemMessengerHelper();
        $messenger = $this->messengerWithMessages([]);

        $helper->setMessenger($messenger);

        self::assertSame($messenger, $helper->getMessenger());
    }

    /**
     * @param array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>> $messages
     * @throws \PHPUnit\Exception
     */
    private function messengerWithMessages(array $messages): SystemMessenger
    {
        $storage = [SystemMessengerInterface::SESSION_KEY => $messages];
        $session = $this->createStub(SessionInterface::class);
        $session->method('get')
            ->willReturnCallback(
                static fn(string $name, mixed $default = null): mixed => $storage[$name] ?? $default,
            );
        $session->method('has')
            ->willReturnCallback(
                static fn(string $name): bool => array_key_exists($name, $storage),
            );

        return new SystemMessenger($session, SystemMessengerInterface::SESSION_KEY);
    }
}
