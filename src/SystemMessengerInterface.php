<?php

declare(strict_types=1);

/**
 * Derived from mezzio/mezzio-flash package @copyright Copyright (c) Laminas Project.
 */

namespace Webware\Message;

/**
 * @api
 */
interface SystemMessengerInterface
{
    public const string MESSAGE_TEMPLATES = 'message_templates';
    public const string SESSION_KEY       = self::class . '::SYSTEM_MESSENGER_NEXT';

    public function addHop(): void;

    public function clearMessages(): void;

    /**
     * @throws Exception\InvalidHopsValueException
     */
    public function danger(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void;

    /**
     * @param list<string> $default
     * @return list<string>
     */
    public function getMessage(MessageLevel $key, array $default = []): array;

    /**
     * @return array<string, list<string>>
     */
    public function getMessages(): array;

    public function hasMessages(): bool;

    /**
     * @throws Exception\InvalidHopsValueException
     */
    public function info(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void;

    /**
     * @throws Exception\InvalidHopsValueException
     */
    public function send(
        string $message,
        MessageLevel $key = MessageLevel::Info,
        ?int $hops = 1,
        string|int|null $id = null,
    ): void;

    /**
     * @throws Exception\InvalidHopsValueException
     */
    public function sendNow(
        string $message,
        MessageLevel $key = MessageLevel::Info,
        ?int $hops = 0,
        string|int|null $id = null,
    ): void;

    /**
     * @throws Exception\InvalidHopsValueException
     */
    public function success(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void;

    /**
     * @throws Exception\InvalidHopsValueException
     */
    public function warning(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void;
}
