<?php

declare(strict_types=1);

/**
 * Derived from mezzio/mezzio-flash package @copyright Copyright (c) Laminas Project.
 */

namespace Webware\Message;

interface SystemMessengerInterface
{
    public const MESSAGE_TEMPLATES = 'message_templates';
    public const SESSION_KEY       = self::class . '::SYSTEM_MESSENGER_NEXT';

    public function addHop(): void;

    public function clearMessages(): void;

    public function danger(string $message, ?int $hops = 1, bool $now = true, string|int|null $id = null): void;

    public function getMessage(MessageLevel $key, array $default = []): array;

    public function getMessages(): array;

    public function hasMessages(): bool;

    public function info(string $message, ?int $hops = 1, bool $now = true, string|int|null $id = null): void;

    public function send(
        string $message,
        MessageLevel $key = MessageLevel::Info,
        ?int $hops = 1,
        string|int|null $id = null,
    ): void;

    public function sendNow(
        string $message,
        MessageLevel $key = MessageLevel::Info,
        ?int $hops = 1,
        string|int|null $id = null,
    ): void;

    public function success(string $message, ?int $hops = 1, bool $now = true, string|int|null $id = null): void;

    public function warning(string $message, ?int $hops = 1, bool $now = true, string|int|null $id = null): void;
}
