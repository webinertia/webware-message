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

use Mezzio\Session\SessionInterface;
use Override;

use function is_array;

/**
 * original code by Mezzio\Flash
 */
final class SystemMessenger implements SystemMessengerInterface
{
    /** @var array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>> */
    private array $currentMessages = [];

    public function __construct(
        private SessionInterface $session,
        private string $sessionKey,
    ) {
        $this->prepareMessages($session, $sessionKey);
    }

    /**
     * Prolongs any current messages for one more hop.
     */
    #[Override]
    public function addHop(): void
    {
        $messages = [];

        foreach ($this->currentMessages as $key => $list) {
            $messages[$key] = [];
            foreach ($list as $data) {
                if (0 < $data['hops']) {
                    $messages[$key][] = $data;
                    continue;
                }
                $data['hops']++;
                $messages[$key][] = $data;
            }
        }

        $this->currentMessages = $messages;
    }

    /**
     * Clear all message values.
     *
     * Affects the next and subsequent requests.
     */
    #[Override]
    public function clearMessages(): void
    {
        $this->session->unset($this->sessionKey);
    }

    /**
     * @throws Exception\InvalidHopsValueException
     */
    #[Override]
    public function danger(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void
    {
        $now
            ? $this->sendNow($message, MessageLevel::Danger, $hops, $id)
            : $this->send($message, MessageLevel::Danger, $hops, $id);
    }

    /**
     * Retrieve a message value.
     *
     * Will return a value only if a message value was set in a previous request,
     * or if `sendNow()` was called in this request with the same `$key`.
     *
     * WILL NOT return a value if set in the current request via `send()`.
     *
     * @param list<string> $default Default value to return if no message value exists.
     * @return list<string>
     */
    #[Override]
    public function getMessage(MessageLevel|string $key, array $default = []): array
    {
        //return $this->currentMessages[$key instanceof MessageLevel ? $key->value : $key] ?? $default;
        return $this->getMessages()[$key instanceof MessageLevel ? $key->value : $key] ?? $default;
    }

    /**
     * Retrieve all message values.
     *
     * Will return all values was set in a previous request, or if `sendNow()`
     * was called in this request.
     *
     * WILL NOT return values set in the current request via `send()`.
     *
     * @return array<string, list<string>>
     */
    #[Override]
    public function getMessages(): array
    {
        $messages = [];

        foreach ($this->currentMessages as $key => $list) {
            $values = [];
            foreach ($list as $data) {
                $values[] = $data['message'];
            }
            $messages[$key] = $values;
        }

        return $messages;
    }

    #[Override]
    public function hasMessages(): bool
    {
        return [] !== $this->currentMessages;
    }

    /**
     * @throws Exception\InvalidHopsValueException
     */
    #[Override]
    public function info(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void
    {
        $now
            ? $this->sendNow($message, MessageLevel::Info, $hops, $id)
            : $this->send($message, MessageLevel::Info, $hops, $id);
    }

    public function prepareMessages(SessionInterface $session, string $sessionKey): void
    {
        $hasSessionMessages = $session->has($sessionKey);

        if (! $hasSessionMessages) {
            return;
        }

        $sessionMessages = $this->getStoredMessages($sessionKey);

        foreach ($sessionMessages as $key => $list) {
            $kept = [];
            foreach ($list as $data) {
                if (0 === $data['hops']) {
                    continue;
                }
                $data['hops']--;
                $kept[] = $data;
            }

            if ([] === $kept) {
                unset($sessionMessages[$key]);
                continue;
            }

            $sessionMessages[$key] = $kept;
        }

        [] === $sessionMessages
            ? $session->unset($sessionKey)
            : $session->set($sessionKey, $sessionMessages);

        $this->currentMessages = $sessionMessages;
    }

    /**
     * Set a Message value with the given key.
     *
     * Message values are accessible on the next "hop", where a hop is the
     * next time the session is accessed; you may pass an additional $hops
     * integer to allow access for more than one hop.
     *
     * @throws Exception\InvalidHopsValueException
     */
    #[Override]
    public function send(
        string $message,
        MessageLevel|string $key = MessageLevel::Info,
        ?int $hops = 1,
        string|int|null $id = null,
    ): void {
        $hops ??= 1;

        if (1 > $hops) {
            throw Exception\InvalidHopsValueException::valueTooLow(
                $key instanceof MessageLevel ? $key->value : $key,
                $hops,
            );
        }

        $messages                                                      = $this->getStoredMessages();
        $messages[$key instanceof MessageLevel ? $key->value : $key][] = [
            'message' => $message,
            'hops'    => $hops,
            'key'     => $key instanceof MessageLevel ? $key->value : $key,
            'id'      => $id,
        ];
        $this->session->set($this->sessionKey, $messages);
    }

    /**
     * Set a Message value with the given key, but allow access during this request.
     *
     * Message values are generally accessible only on subsequent requests;
     * using this method, you may make the value available during the current
     * request as well.
     *
     * If you want the value to be visible only in the current request, you may
     * pass zero as the third argument.
     *
     * @throws Exception\InvalidHopsValueException
     */
    #[Override]
    public function sendNow(
        string $message,
        MessageLevel|string $key = MessageLevel::Info,
        ?int $hops = 0,
        string|int|null $id = null,
    ): void {
        $this->currentMessages[$key instanceof MessageLevel ? $key->value : $key][] = [
            'message' => $message,
            'hops'    => 0,
            'key'     => $key instanceof MessageLevel ? $key->value : $key,
            'id'      => $id,
        ];
        if (0 < ($hops ?? 1)) {
            $this->send($message, $key, $hops, $id);
        }
    }

    /**
     * @throws Exception\InvalidHopsValueException
     */
    #[Override]
    public function success(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void
    {
        $now
            ? $this->sendNow($message, MessageLevel::Success, $hops, $id)
            : $this->send($message, MessageLevel::Success, $hops, $id);
    }

    /**
     * @throws Exception\InvalidHopsValueException
     */
    #[Override]
    public function warning(string $message, ?int $hops = 0, bool $now = true, string|int|null $id = null): void
    {
        $now
            ? $this->sendNow($message, MessageLevel::Warning, $hops, $id)
            : $this->send($message, MessageLevel::Warning, $hops, $id);
    }

    /**
     * @return array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>>
     */
    private function getStoredMessages(?string $sessionKey = null): array
    {
        /** @var array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>>|null $messages */
        $messages = $this->session->get($sessionKey ?? $this->sessionKey, []);

        if (! is_array($messages)) {
            return [];
        }

        return $messages;
    }
}
