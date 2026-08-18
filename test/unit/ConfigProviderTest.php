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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Webware\Message\ConfigProvider;
use Webware\Message\Middleware\MessageMiddleware;
use Webware\Message\Middleware\MessageMiddlewareFactory;
use Webware\Message\SystemMessenger;
use Webware\Message\SystemMessengerInterface;
use Webware\Message\View\Helper\SystemMessenger as SystemMessengerHelper;
use Webware\Message\View\Helper\SystemMessengerFactory;

use function dirname;

#[CoversClass(ConfigProvider::class)]
#[CoversMethod(ConfigProvider::class, 'getDependencies')]
#[CoversMethod(ConfigProvider::class, 'getMessageTemplates')]
#[CoversMethod(ConfigProvider::class, 'getTemplates')]
#[CoversMethod(ConfigProvider::class, 'getViewHelpers')]
#[CoversMethod(ConfigProvider::class, '__invoke')]
final class ConfigProviderTest extends TestCase
{
    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getDependenciesReturnsMessengerAliasAndMiddlewareFactory(): void
    {
        $provider = new ConfigProvider();

        self::assertSame(
            [
                'aliases' => [
                    SystemMessengerInterface::class => SystemMessenger::class,
                ],
                'factories' => [
                    MessageMiddleware::class => MessageMiddlewareFactory::class,
                ],
            ],
            $provider->getDependencies(),
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getMessageTemplatesReturnsEmptyTemplateMapByDefault(): void
    {
        $provider = new ConfigProvider();

        self::assertSame(
            [
                SystemMessengerInterface::MESSAGE_TEMPLATES => [],
            ],
            $provider->getMessageTemplates(),
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getTemplatesRegistersMessageTemplatePath(): void
    {
        $provider = new ConfigProvider();

        /** @var array{paths: array{message: list<string>}} $templates */
        $templates = $provider->getTemplates();

        self::assertSame(
            [dirname(__DIR__, levels: 2) . '/src/../templates/'],
            $templates['paths']['message'],
        );
        self::assertCount(1, $templates['paths']);
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getViewHelpersRegistersHelperAliasesAndFactory(): void
    {
        $provider = new ConfigProvider();

        self::assertSame(
            [
                'aliases' => [
                    'messenger' => SystemMessengerHelper::class,
                    'systemMessage' => SystemMessengerHelper::class,
                    'systemMessenger' => SystemMessengerHelper::class,
                ],
                'factories' => [
                    SystemMessengerHelper::class => SystemMessengerFactory::class,
                ],
            ],
            $provider->getViewHelpers(),
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function invokeMergesAllConfigurationSections(): void
    {
        $provider = new ConfigProvider();

        $config = $provider();

        self::assertSame(
            [
                'dependencies' => $provider->getDependencies(),
                'templates' => $provider->getTemplates(),
                'view_helpers' => $provider->getViewHelpers(),
                SystemMessengerInterface::class => $provider->getMessageTemplates(),
            ],
            $config,
        );
    }
}
