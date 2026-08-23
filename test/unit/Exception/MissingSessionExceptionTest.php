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

namespace WebwareTest\Message\Exception;

use Laminas\Diactoros\Response;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;
use Webware\Message\Exception\ExceptionInterface;
use Webware\Message\Exception\MissingSessionException;

use function str_contains;

#[CoversClass(MissingSessionException::class)]
#[CoversMethod(MissingSessionException::class, 'forMiddleware')]
final class MissingSessionExceptionTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function exceptionExtendsRuntimeExceptionAndPackageMarker(): void
    {
        $middleware = $this->createStub(MiddlewareInterface::class);
        $exception  = MissingSessionException::forMiddleware($middleware);

        self::assertInstanceOf(RuntimeException::class, $exception);
        self::assertInstanceOf(ExceptionInterface::class, $exception);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function forMiddlewareNamesOffendingMiddleware(): void
    {
        $middleware = new class implements MiddlewareInterface {
            #[Override]
            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler,
            ): ResponseInterface {
                return new Response();
            }
        };

        $exception = MissingSessionException::forMiddleware($middleware);

        self::assertTrue(str_contains($exception->getMessage(), $middleware::class));
        self::assertTrue(str_contains($exception->getMessage(), 'missing session attribute'));
    }
}
