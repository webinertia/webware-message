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

namespace Webware\Message\Middleware;

use Override;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webware\Message\Exception\InvalidHopsValueException;
use Webware\Message\MessageLevel;
use Webware\Message\NotificationCapableInterface;
use Webware\Message\SystemMessengerInterface;
use Webware\MessageBus\Command\CommandResult;
use Webware\MessageBus\MessageStatus;

/**
 * Translates a completed command result into a flash notification.
 */
final readonly class NotificationMiddleware implements MiddlewareInterface
{
    /**
     * @throws InvalidHopsValueException
     */
    #[Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        /** @var CommandResult|null $result */
        $result = $request->getAttribute(CommandResult::class);

        /** @var SystemMessengerInterface|null $messenger */
        $messenger = $request->getAttribute(SystemMessengerInterface::class);

        if (! $result instanceof CommandResult || ! $messenger instanceof SystemMessengerInterface) {
            return $handler->handle($request);
        }

        $command = $result->getCommand();

        if (! $command instanceof NotificationCapableInterface) {
            return $handler->handle($request);
        }

        $isSuccess = $result->getStatus() === MessageStatus::Success;
        $message   = $isSuccess ? $command->successMessage : $command->failureMessage;
        $level     = $isSuccess ? MessageLevel::Success : MessageLevel::Warning;

        $messenger->sendNow(
            message: $message,
            key    : $level,
            hops   : 0,
        );

        return $handler->handle($request);
    }
}
