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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\Exception\ExceptionInterface;
use Webware\Message\Exception\InvalidSystemMessengerImplementationException;
use Webware\Message\Middleware\MessageMiddleware;
use Webware\Message\SystemMessengerInterface;

use function str_contains;

#[CoversClass(InvalidSystemMessengerImplementationException::class)]
#[CoversMethod(InvalidSystemMessengerImplementationException::class, 'forClass')]
final class InvalidSystemMessengerImplementationExceptionTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function exceptionImplementsPackageMarkerInterface(): void
    {
        self::assertInstanceOf(
            ExceptionInterface::class,
            InvalidSystemMessengerImplementationException::forClass(TestCase::class),
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function forClassNamesOffendingClassAndContract(): void
    {
        $exception = InvalidSystemMessengerImplementationException::forClass(TestCase::class);

        self::assertTrue(str_contains($exception->getMessage(), TestCase::class));
        self::assertTrue(str_contains($exception->getMessage(), MessageMiddleware::class));
        self::assertTrue(str_contains($exception->getMessage(), SystemMessengerInterface::class));
    }
}
