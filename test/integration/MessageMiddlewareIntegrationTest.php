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

namespace WebwareTestIntegration\Message;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Mezzio\Session\Ext\PhpSessionPersistence;
use Mezzio\Session\SessionIdentifierAwareInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Override;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webware\Message\Exception\MissingSessionException;
use Webware\Message\MessageLevel;
use Webware\Message\Middleware\MessageMiddleware;
use Webware\Message\SystemMessenger;
use Webware\Message\SystemMessengerInterface;
use Webware\Message\View\Helper\SystemMessenger as SystemMessengerHelper;

use function preg_match;
use function session_id;
use function session_status;
use function session_write_close;

use const PHP_SESSION_ACTIVE;

#[CoversClass(MessageMiddleware::class)]
#[CoversMethod(MessageMiddleware::class, 'process')]
#[CoversClass(SystemMessenger::class)]
#[CoversMethod(SystemMessenger::class, '__construct')]
#[CoversMethod(SystemMessenger::class, 'prepareMessages')]
#[CoversMethod(SystemMessenger::class, 'send')]
#[CoversMethod(SystemMessenger::class, 'getMessage')]
final class MessageMiddlewareIntegrationTest extends TestCase
{
    /**
     * @throws MissingSessionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function messageStoredInFirstRequestSurvivesToSecondRequest(): void
    {
        $persistence = new PhpSessionPersistence();
        $middleware  = new MessageMiddleware(new SystemMessengerHelper());

        $firstRequest = new ServerRequest();
        /** @var SessionInterface&SessionIdentifierAwareInterface $firstSession */
        $firstSession = $persistence->initializeSessionFromRequest($firstRequest);
        $firstRequest = $firstRequest->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $firstSession);

        $sendHandler = new class implements RequestHandlerInterface {
            /**
             * @throws \PHPUnit\Exception
             */
            #[Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /** @var SystemMessenger|null $messenger */
                $messenger = $request->getAttribute(SystemMessengerInterface::class);
                if (! $messenger instanceof SystemMessenger) {
                    Assert::fail('Expected a SystemMessenger instance in the request attribute.');
                }
                $messenger->send('Persisted integration message', MessageLevel::Success);

                return new Response();
            }
        };

        $firstResponse = $persistence->persistSession(
            $firstSession,
            $middleware->process($firstRequest, $sendHandler),
        );

        $sessionId = $this->sessionIdFromCookie($firstResponse);

        $secondRequest = new ServerRequest()->withHeader('Cookie', "PHPSESSID={$sessionId}");
        /** @var SessionInterface&SessionIdentifierAwareInterface $secondSession */
        $secondSession = $persistence->initializeSessionFromRequest($secondRequest);
        $secondRequest = $secondRequest->withAttribute(SessionMiddleware::SESSION_ATTRIBUTE, $secondSession);

        $readHandler = new class implements RequestHandlerInterface {
            /**
             * @throws \PHPUnit\Exception
             */
            #[Override]
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                /** @var SystemMessenger|null $messenger */
                $messenger = $request->getAttribute(SystemMessengerInterface::class);
                if (! $messenger instanceof SystemMessenger) {
                    Assert::fail('Expected a SystemMessenger instance in the request attribute.');
                }
                Assert::assertSame(
                    ['Persisted integration message'],
                    $messenger->getMessage(MessageLevel::Success),
                );

                return new Response();
            }
        };

        $persistence->persistSession($secondSession, $middleware->process($secondRequest, $readHandler));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Override]
    protected function tearDown(): void
    {
        $sessionStatus = session_status();

        if (PHP_SESSION_ACTIVE === $sessionStatus) {
            session_write_close();
        }

        session_id('');
    }

    /**
     * @throws \PHPUnit\Exception
     */
    private function sessionIdFromCookie(ResponseInterface $response): string
    {
        $matches = [];

        self::assertSame(1, preg_match('/^[^=]+=([^;]+)/', $response->getHeaderLine('Set-Cookie'), $matches));

        return $matches[1] ?? '';
    }
}
