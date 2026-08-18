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

namespace WebwareTest\Message\Middleware;

use Laminas\Diactoros\ServerRequest;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webware\Message\Exception\MissingSessionException;
use Webware\Message\Middleware\MessageMiddleware;
use Webware\Message\SystemMessenger;
use Webware\Message\SystemMessengerInterface;
use Webware\Message\View\Helper\SystemMessenger as SystemMessengerHelper;

#[CoversClass(MessageMiddleware::class)]
#[CoversMethod(MessageMiddleware::class, '__construct')]
#[CoversMethod(MessageMiddleware::class, 'process')]
final class MessageMiddlewareTest extends TestCase
{
    /**
     * @throws MissingSessionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processInjectsMessengerIntoHelperAndRequestAttribute(): void
    {
        $helper = new SystemMessengerHelper();
        $session = $this->createStub(SessionInterface::class);
        $request = new ServerRequest()->withAttribute(
            SessionMiddleware::SESSION_ATTRIBUTE,
            $session,
        );

        $response = $this->createStub(ResponseInterface::class);
        $capturedMessenger = null;
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturnCallback(
                static function (ServerRequestInterface $request) use (
                    &$capturedMessenger,
                    $response,
                ): ResponseInterface {
                    /** @var SystemMessenger|null $capturedMessenger */
                    $capturedMessenger = $request->getAttribute(SystemMessengerInterface::class);

                    return $response;
                },
            );

        $middleware = new MessageMiddleware($helper);

        self::assertSame($response, $middleware->process($request, $handler));
        self::assertInstanceOf(SystemMessenger::class, $capturedMessenger);
        self::assertNotNull($helper->getMessenger());
    }

    /**
     * @throws MissingSessionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function processThrowsWhenSessionAttributeIsMissing(): void
    {
        $helper = new SystemMessengerHelper();
        $handler = $this->createStub(RequestHandlerInterface::class);

        $this->expectException(MissingSessionException::class);

        new MessageMiddleware($helper)->process(new ServerRequest(), $handler);
    }
}
