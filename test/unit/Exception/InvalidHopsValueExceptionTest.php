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

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\Exception\ExceptionInterface;
use Webware\Message\Exception\InvalidHopsValueException;

#[CoversClass(InvalidHopsValueException::class)]
#[CoversMethod(InvalidHopsValueException::class, 'valueTooLow')]
final class InvalidHopsValueExceptionTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function exceptionExtendsInvalidArgumentExceptionAndPackageMarker(): void
    {
        self::assertInstanceOf(InvalidArgumentException::class, InvalidHopsValueException::valueTooLow('info', 0));
        self::assertInstanceOf(ExceptionInterface::class, InvalidHopsValueException::valueTooLow('info', 0));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function valueTooLowBuildsMessageWithKeyAndReceivedHops(): void
    {
        $exception = InvalidHopsValueException::valueTooLow('info', 0);

        self::assertSame(
            'Hops value specified for message "info" was too low; must be greater than 0, received 0',
            $exception->getMessage(),
        );
    }
}
