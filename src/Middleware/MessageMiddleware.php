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

namespace Webware\Message\Middleware;

use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Webware\Message\SystemMessenger;
use Webware\Message\SystemMessengerInterface;
use Webware\Message\View\Helper\SystemMessenger as Helper;

final class MessageMiddleware implements MiddlewareInterface
{
    public function __construct(
        private Helper $helper,
    ) {}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // create an instance of the SystemMessenger
        $systemMessenger = new SystemMessenger(
            $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE),
            SystemMessengerInterface::SESSION_KEY,
        );
        // inject SystemMessenger into the helper instance
        $this->helper->setMessenger($systemMessenger);

        // next in the stack
        return $handler->handle($request->withAttribute(SystemMessengerInterface::class, $systemMessenger));
    }
}
