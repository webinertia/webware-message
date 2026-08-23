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

namespace Webware\Message\View\Helper;

use Webware\Message\MessageIcon;
use Webware\Message\MessageLevel;
use Webware\Message\SystemMessenger as Messenger;
use Webware\Message\SystemMessengerInterface;

use function array_key_exists;
use function implode;
use function sprintf;

final class SystemMessenger
{
    final public const string MESSAGE_KEY = SystemMessengerInterface::SESSION_KEY;

    private const string MESSAGE_TOAST = <<<'EOT'
            <div class="toast" role="alert" data-bs-autohide="false" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-%s-subtle">
                    <i class="text-%s bi bi-%s"></i>
                    <strong class="ms-auto me-auto">%s</strong>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body">
                    %s
                </div>
            </div>
        EOT;

    private ?Messenger $messenger = null;

    public function getMessenger(): ?Messenger
    {
        return $this->messenger;
    }

    public function setMessenger(Messenger $messenger): void
    {
        $this->messenger = $messenger;
    }

    public function __invoke(): string
    {
        if (null === $this->messenger) {
            return '';
        }

        $levels   = MessageLevel::cases();
        $messages = '';
        foreach ($levels as $key) {
            $systemMessages = $this->messenger->getMessages();
            if (! array_key_exists($key->value, $systemMessages)) {
                continue;
            }
            $messages .= sprintf(
                static::MESSAGE_TOAST,
                $key->value,
                $key->value,
                MessageIcon::tryFromLevel($key->value)->value,
                $key->name,
                implode('<br>', $systemMessages[$key->value]),
            );
        }

        return $messages;
    }
}
