<?php

declare(strict_types=1);

/**
 * This file is part of the Webware Message package.
 *
 * Copyright (c) 2026 Joey Smith <jsmith@webinertia.net>
 * and contributors.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace WebwareTest\Message\Middleware;

use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webware\Message\MessageLevel;
use Webware\Message\Middleware\NotificationMiddleware;
use Webware\Message\NotificationCapableInterface;
use Webware\Message\SystemMessengerInterface;
use Webware\MessageBus\Command\CommandInterface;
use Webware\MessageBus\Command\CommandResult;
use Webware\MessageBus\MessageStatus;

#[CoversClass(NotificationMiddleware::class)]
#[CoversMethod(NotificationMiddleware::class, 'process')]
final class NotificationMiddlewareTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processDoesNotNotifyWhenCommandIsNotNotificationCapable(): void
    {
        $response  = $this->createStub(ResponseInterface::class);
        $messenger = $this->createMock(SystemMessengerInterface::class);
        $messenger->expects($this->never())->method('sendNow');

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $request = new ServerRequest()->withAttribute(SystemMessengerInterface::class, $messenger)
            ->withAttribute(
                CommandResult::class,
                new CommandResult($this->createStub(CommandInterface::class), MessageStatus::Success, null),
            );

        self::assertSame($response, new NotificationMiddleware()->process($request, $handler));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processPushesFailureNotification(): void
    {
        $response        = $this->createStub(ResponseInterface::class);
        $capturedLevel   = null;
        $capturedMessage = null;

        $messenger = $this->createMock(SystemMessengerInterface::class);
        $messenger->expects($this->once())
            ->method('sendNow')
            ->willReturnCallback(
                static function (string $message, MessageLevel $key) use (&$capturedLevel, &$capturedMessage): void {
                    $capturedLevel   = $key;
                    $capturedMessage = $message;
                },
            );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $request = new ServerRequest()->withAttribute(SystemMessengerInterface::class, $messenger)
            ->withAttribute(
                CommandResult::class,
                new CommandResult($this->command('Role saved.', 'Role not saved.'), MessageStatus::Failure, null),
            );

        self::assertSame($response, new NotificationMiddleware()->process($request, $handler));
        self::assertSame('Role not saved.', $capturedMessage);
        self::assertSame(MessageLevel::Warning, $capturedLevel);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processPushesSuccessNotification(): void
    {
        $response        = $this->createStub(ResponseInterface::class);
        $capturedLevel   = null;
        $capturedMessage = null;

        $messenger = $this->createMock(SystemMessengerInterface::class);
        $messenger->expects($this->once())
            ->method('sendNow')
            ->willReturnCallback(
                static function (string $message, MessageLevel $key) use (&$capturedLevel, &$capturedMessage): void {
                    $capturedLevel   = $key;
                    $capturedMessage = $message;
                },
            );

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $request = new ServerRequest()->withAttribute(SystemMessengerInterface::class, $messenger)
            ->withAttribute(
                CommandResult::class,
                new CommandResult($this->command('Role saved.', 'Role not saved.'), MessageStatus::Success, null),
            );

        self::assertSame($response, new NotificationMiddleware()->process($request, $handler));
        self::assertSame('Role saved.', $capturedMessage);
        self::assertSame(MessageLevel::Success, $capturedLevel);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processReturnsHandlerResponseWhenMessengerAttributeIsMissing(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        $request = new ServerRequest()->withAttribute(
            CommandResult::class,
            new CommandResult($this->command('saved', 'failed'), MessageStatus::Success, null),
        );

        self::assertSame($response, new NotificationMiddleware()->process($request, $handler));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processReturnsHandlerResponseWhenResultAttributeIsMissing(): void
    {
        $response = $this->createStub(ResponseInterface::class);
        $handler  = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($response);

        self::assertSame($response, new NotificationMiddleware()->process(new ServerRequest(), $handler));
    }

    private function command(string $success, string $failure): CommandInterface&NotificationCapableInterface
    {
        return new class($success, $failure) implements CommandInterface, NotificationCapableInterface {
            public string $successMessage {
                get => $this->success;
            }

            public string $failureMessage {
                get => $this->failure;
            }

            public function __construct(
                private string $success,
                private string $failure,
            ) {}
        };
    }
}
