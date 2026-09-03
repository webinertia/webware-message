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

namespace Webware\Message;

final readonly class ConfigProvider
{
    /** @return array<string, mixed> */
    public function getDependencies(): array
    {
        return [
            'aliases'    => [
                SystemMessengerInterface::class => SystemMessenger::class,
            ],
            'factories'  => [
                Middleware\MessageMiddleware::class => Middleware\MessageMiddlewareFactory::class,
            ],
            'invokables' => [
                Middleware\NotificationMiddleware::class => Middleware\NotificationMiddleware::class,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'message' => [__DIR__ . '/../templates/'],
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function getViewHelpers(): array
    {
        return [
            'aliases'   => [
                'messenger'       => View\Helper\SystemMessenger::class,
                'systemMessage'   => View\Helper\SystemMessenger::class,
                'systemMessenger' => View\Helper\SystemMessenger::class,
            ],
            'factories' => [
                View\Helper\SystemMessenger::class => View\Helper\SystemMessengerFactory::class,
            ],
        ];
    }

    /** @return array<string, mixed> */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates'    => $this->getTemplates(),
            'view_helpers' => $this->getViewHelpers(),
        ];
    }
}
