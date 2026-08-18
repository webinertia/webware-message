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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\View\Helper\SystemMessenger;
use Webware\Message\View\Helper\SystemMessengerFactory;

#[CoversClass(SystemMessengerFactory::class)]
#[CoversMethod(SystemMessengerFactory::class, '__invoke')]
final class SystemMessengerFactoryTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function factoryCreatesHelper(): void
    {
        self::assertInstanceOf(SystemMessenger::class, (new SystemMessengerFactory())());
    }
}
