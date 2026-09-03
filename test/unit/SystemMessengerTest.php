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

use Mezzio\Session\SessionInterface;
use Override;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Webware\Message\Exception\InvalidHopsValueException;
use Webware\Message\MessageLevel;
use Webware\Message\SystemMessenger;
use Webware\Message\SystemMessengerInterface;

use function array_key_exists;
use function sprintf;

#[CoversClass(SystemMessenger::class)]
#[CoversMethod(SystemMessenger::class, '__construct')]
#[CoversMethod(SystemMessenger::class, 'addHop')]
#[CoversMethod(SystemMessenger::class, 'clearMessages')]
#[CoversMethod(SystemMessenger::class, 'danger')]
#[CoversMethod(SystemMessenger::class, 'getMessage')]
#[CoversMethod(SystemMessenger::class, 'getMessages')]
#[CoversMethod(SystemMessenger::class, 'hasMessages')]
#[CoversMethod(SystemMessenger::class, 'info')]
#[CoversMethod(SystemMessenger::class, 'prepareMessages')]
#[CoversMethod(SystemMessenger::class, 'send')]
#[CoversMethod(SystemMessenger::class, 'sendNow')]
#[CoversMethod(SystemMessenger::class, 'success')]
#[CoversMethod(SystemMessenger::class, 'warning')]
final class SystemMessengerTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $storage = [];

    private SessionInterface $session;

    /**
     * @throws ReflectionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function addHopProcessesEntriesAfterAHopsMessage(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'Fresh', 'hops' => 2, 'key' => 'success', 'id' => null],
                ['message' => 'Sticky', 'hops' => 1, 'key' => 'success', 'id' => null],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);
        $messenger->addHop();

        /** @var array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>> $current */
        $current = new ReflectionProperty(SystemMessenger::class, 'currentMessages')->getValue($messenger);

        self::assertSame(
            [
                'success' => [
                    ['message' => 'Fresh', 'hops' => 1, 'key' => 'success', 'id' => null],
                    ['message' => 'Sticky', 'hops' => 1, 'key' => 'success', 'id' => null],
                ],
            ],
            $current,
        );
    }

    /**
     * @throws ReflectionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function addHopProlongsOnlyZeroHopMessages(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'Fresh', 'hops' => 1, 'key' => 'success', 'id' => null],
                ['message' => 'Sticky', 'hops' => 2, 'key' => 'success', 'id' => null],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);
        $messenger->addHop();

        /** @var array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>> $current */
        $current = new ReflectionProperty(SystemMessenger::class, 'currentMessages')->getValue($messenger);

        self::assertSame(
            [
                'success' => [
                    ['message' => 'Fresh', 'hops' => 1, 'key' => 'success', 'id' => null],
                    ['message' => 'Sticky', 'hops' => 1, 'key' => 'success', 'id' => null],
                ],
            ],
            $current,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function addHopProlongsZeroHopCurrentMessages(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->sendNow('Short lived', MessageLevel::Info);
        $messenger->addHop();

        self::assertSame(['Short lived'], $messenger->getMessage(MessageLevel::Info));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function clearMessagesRemovesSessionData(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->send('To be cleared', MessageLevel::Success);

        self::assertTrue(array_key_exists(SystemMessengerInterface::SESSION_KEY, $this->storage));

        $messenger->clearMessages();

        self::assertFalse(array_key_exists(SystemMessengerInterface::SESSION_KEY, $this->storage));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function constructorDecrementsStoredMessageHops(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'Saved', 'hops' => 2, 'key' => 'success', 'id' => null],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(
            ['Saved'],
            $messenger->getMessage(MessageLevel::Success),
        );
        self::assertSame(
            [
                'success' => [
                    ['message' => 'Saved', 'hops' => 1, 'key' => 'success', 'id' => null],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function constructorKeepsMessagesWithRemainingHops(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'info' => [
                ['message' => 'Notice', 'hops' => 1, 'key' => 'info', 'id' => 7],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(['Notice'], $messenger->getMessage(MessageLevel::Info));
        self::assertSame(
            [
                'info' => [
                    ['message' => 'Notice', 'hops' => 0, 'key' => 'info', 'id' => 7],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function constructorRemovesExpiredMessagesAndClearsEmptySessions(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'Expired', 'hops' => 0, 'key' => 'success', 'id' => null],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertFalse($messenger->hasMessages());
        self::assertFalse(array_key_exists(SystemMessengerInterface::SESSION_KEY, $this->storage));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getMessageReturnsDefaultWhenLevelMissing(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame([], $messenger->getMessage(MessageLevel::Danger));
        self::assertSame(['fallback'], $messenger->getMessage(MessageLevel::Danger, ['fallback']));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function getMessagesReturnsOnlyMessageStringsKeyedByLevel(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'First', 'hops' => 1, 'key' => 'success', 'id' => null],
                ['message' => 'Second', 'hops' => 1, 'key' => 'success', 'id' => null],
            ],
            'info'    => [
                ['message' => 'Notice', 'hops' => 1, 'key' => 'info', 'id' => null],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(
            [
                'success' => ['First', 'Second'],
                'info'    => ['Notice'],
            ],
            $messenger->getMessages(),
        );
    }

    /**
     * @throws AssertionFailedError
     * @throws ReflectionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function hopParameterDefaultsMatchIntendedSemantics(): void
    {
        self::assertSame(0, $this->hopsDefault('danger'));
        self::assertSame(0, $this->hopsDefault('info'));
        self::assertSame(0, $this->hopsDefault('success'));
        self::assertSame(0, $this->hopsDefault('warning'));
        self::assertSame(1, $this->hopsDefault('send'));
        self::assertSame(0, $this->hopsDefault('sendNow'));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function levelProxyMethodsSendForCurrentRequestByDefault(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->danger('Danger message');
        $messenger->info('Info message');
        $messenger->success('Success message');
        $messenger->warning('Warning message');

        self::assertSame(['Danger message'], $messenger->getMessage(MessageLevel::Danger));
        self::assertSame(['Info message'], $messenger->getMessage(MessageLevel::Info));
        self::assertSame(['Success message'], $messenger->getMessage(MessageLevel::Success));
        self::assertSame(['Warning message'], $messenger->getMessage(MessageLevel::Warning));

        $nextRequest = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertFalse($nextRequest->hasMessages());
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function levelProxyMethodsStoreForNextRequestWhenNowIsFalse(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->danger('Later danger', 1, false);
        $messenger->info('Later info', 1, false);
        $messenger->success('Later success', 1, false);
        $messenger->warning('Later warning', 1, false);

        self::assertFalse($messenger->hasMessages());

        $nextRequest = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(['Later danger'], $nextRequest->getMessage(MessageLevel::Danger));
        self::assertSame(['Later info'], $nextRequest->getMessage(MessageLevel::Info));
        self::assertSame(['Later success'], $nextRequest->getMessage(MessageLevel::Success));
        self::assertSame(['Later warning'], $nextRequest->getMessage(MessageLevel::Warning));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function prepareMessagesContinuesAfterClearingAnExpiredLevel(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'Expired', 'hops' => 0, 'key' => 'success', 'id' => null],
            ],
            'info'    => [
                ['message' => 'Notice', 'hops' => 1, 'key' => 'info', 'id' => null],
            ],
        ];

        new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(
            [
                'info' => [
                    ['message' => 'Notice', 'hops' => 0, 'key' => 'info', 'id' => null],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function prepareMessagesIsPubliclyCallableAndDecrementsStoredHops(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'info' => [
                ['message' => 'Notice', 'hops' => 2, 'key' => 'info', 'id' => null],
            ],
        ];

        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);
        $messenger->prepareMessages($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(
            [
                'info' => [
                    ['message' => 'Notice', 'hops' => 0, 'key' => 'info', 'id' => null],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function prepareMessagesProcessesEachLevelAndEntryIndependently(): void
    {
        $this->storage[SystemMessengerInterface::SESSION_KEY] = [
            'success' => [
                ['message' => 'Expired', 'hops' => 0, 'key' => 'success', 'id' => null],
                ['message' => 'Kept', 'hops' => 1, 'key' => 'success', 'id' => null],
            ],
            'info'    => [
                ['message' => 'Notice', 'hops' => 1, 'key' => 'info', 'id' => null],
            ],
        ];

        new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(
            [
                'success' => [
                    ['message' => 'Kept', 'hops' => 0, 'key' => 'success', 'id' => null],
                ],
                'info'    => [
                    ['message' => 'Notice', 'hops' => 0, 'key' => 'info', 'id' => null],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function prepareMessagesSkipsWorkWhenNoMessagesAreStored(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->method('has')->willReturn(false);
        $session->method('get')->willReturn([]);
        $session->expects($this->never())->method('unset');

        $messenger = new SystemMessenger($session, SystemMessengerInterface::SESSION_KEY);
        $messenger->prepareMessages($session, SystemMessengerInterface::SESSION_KEY);

        self::assertFalse($messenger->hasMessages());
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendNowIsVisibleInCurrentRequestButNotPersistedByDefault(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->sendNow('Instant', MessageLevel::Info);

        self::assertSame(['Instant'], $messenger->getMessage(MessageLevel::Info));

        $nextRequest = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertFalse($nextRequest->hasMessages());
    }

    /**
     * @throws ReflectionException
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendNowStoresCurrentEntryWithZeroHops(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->sendNow('Instant', MessageLevel::Info);

        self::assertSame(['Instant'], $messenger->getMessage(MessageLevel::Info));

        /** @var array<string, list<array{message: string, hops: int, key: string, id: string|int|null}>> $current */
        $current = new ReflectionProperty(SystemMessenger::class, 'currentMessages')->getValue($messenger);

        self::assertSame(
            [
                'info' => [
                    ['message' => 'Instant', 'hops' => 0, 'key' => 'info', 'id' => null],
                ],
            ],
            $current,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendNowWithHopsAlsoPersistsForNextRequest(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->sendNow('Instant plus next', MessageLevel::Success, 1);

        self::assertSame(['Instant plus next'], $messenger->getMessage(MessageLevel::Success));

        $nextRequest = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(['Instant plus next'], $nextRequest->getMessage(MessageLevel::Success));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendNowWithNullHopsPersistsForNextRequest(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->sendNow('Null hops now', MessageLevel::Success, null);

        self::assertSame(['Null hops now'], $messenger->getMessage(MessageLevel::Success));

        $nextRequest = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(['Null hops now'], $nextRequest->getMessage(MessageLevel::Success));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendNowWithNullHopsStoresSingleHop(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->sendNow('Null hops now', MessageLevel::Success, null);

        self::assertSame(
            [
                'success' => [
                    ['message' => 'Null hops now', 'hops' => 1, 'key' => 'success', 'id' => null],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendStoresKeyIdAndHopsMetadata(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->send('Tracked', 'success', 2, 'message-id');

        self::assertSame(
            [
                'success' => [
                    ['message' => 'Tracked', 'hops' => 2, 'key' => 'success', 'id' => 'message-id'],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendStoresMessageForNextRequestOnly(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->send('Next request', MessageLevel::Success);

        self::assertFalse($messenger->hasMessages());
        self::assertSame([], $messenger->getMessage(MessageLevel::Success));

        $nextRequest = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        self::assertSame(['Next request'], $nextRequest->getMessage(MessageLevel::Success));
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendThrowsWhenHopsAreBelowOne(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        try {
            $messenger->send('Never', MessageLevel::Info, 0);
            self::fail('Expected InvalidHopsValueException was not thrown.');
        } catch (InvalidHopsValueException $exception) {
            self::assertSame(
                'Hops value specified for message "info" was too low; must be greater than 0, received 0',
                $exception->getMessage(),
            );
        }
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Test]
    public function sendTreatsNullHopsAsSingleHop(): void
    {
        $messenger = new SystemMessenger($this->session, SystemMessengerInterface::SESSION_KEY);

        $messenger->send('Null hops', MessageLevel::Info, null);

        self::assertSame(
            [
                'info' => [
                    ['message' => 'Null hops', 'hops' => 1, 'key' => 'info', 'id' => null],
                ],
            ],
            $this->storage[SystemMessengerInterface::SESSION_KEY] ?? null,
        );
    }

    /**
     * @throws \PHPUnit\Exception
     */
    #[Override]
    protected function setUp(): void
    {
        $this->storage = [];
        $this->session = $this->createStub(SessionInterface::class);
        $this->session->method('get')
            ->willReturnCallback(
                fn(string $name, mixed $default = null): mixed => $this->storage[$name] ?? $default,
            );
        $this->session->method('has')
            ->willReturnCallback(
                fn(string $name): bool => array_key_exists($name, $this->storage),
            );
        $this->session->method('set')
            ->willReturnCallback(
                function (string $name, mixed $value): void {
                    $this->storage[$name] = $value;
                },
            );
        $this->session->method('unset')
            ->willReturnCallback(
                function (string $name): void {
                    unset($this->storage[$name]);
                },
            );
    }

    /**
     * @throws ReflectionException
     * @throws AssertionFailedError
     */
    private function hopsDefault(string $method): mixed
    {
        $parameters = new ReflectionMethod(SystemMessenger::class, $method)->getParameters();

        foreach ($parameters as $parameter) {
            if ($parameter->getName() === 'hops') {
                return $parameter->getDefaultValue();
            }
        }

        self::fail(sprintf('Method %s has no hops parameter.', $method));
    }
}
