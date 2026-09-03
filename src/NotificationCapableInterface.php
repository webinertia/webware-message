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

namespace Webware\Message;

/**
 * Implemented by messages that expose the user-facing notification text for
 * their success and failure outcomes.
 *
 * @api
 */
interface NotificationCapableInterface
{
    public string $successMessage { get; }

    public string $failureMessage { get; }
}
