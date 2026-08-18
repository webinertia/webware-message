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

use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Webware\Message\Middleware\MessageMiddleware;
use Webware\Message\Middleware\MessageMiddlewareFactory;
use Webware\Message\View\Helper\SystemMessenger;

#[CoversClass(MessageMiddlewareFactory::class)]
#[CoversMethod(MessageMiddlewareFactory::class, '__invoke')]
final class MessageMiddlewareFactoryTest extends TestCase
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function factoryBuildsMiddlewareUsingHelperFromPluginManager(): void
    {
        $helper = new SystemMessenger();
        $helperManager = new HelperPluginManager(
            new ServiceManager(),
            [
                'factories' => [
                    SystemMessenger::class => static fn(): SystemMessenger => $helper,
                ],
            ],
        );

        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnCallback(
                static fn(string $id): mixed => HelperPluginManager::class === $id ? $helperManager : null,
            );

        $middleware = (new MessageMiddlewareFactory())($container);

        self::assertInstanceOf(MessageMiddleware::class, $middleware);
    }
}
