# SystemMessenger

`Webware\Message\SystemMessengerInterface` (aliased to
`Webware\Message\SystemMessenger`) is the flash-messaging API. The middleware
constructs it per request from the session and exposes it as the
`SystemMessengerInterface::class` request attribute.

## Sending

```php
use Webware\Message\MessageLevel;
use Webware\Message\SystemMessengerInterface;

/** @var SystemMessengerInterface $messenger */
$messenger = $request->getAttribute(SystemMessengerInterface::class);

$messenger->send('Stored for the next request', MessageLevel::Success);
$messenger->sendNow('Visible this request too', MessageLevel::Info);
```

### Hops

- `send()` defaults to one hop — visible on the next request, then expires.
- `sendNow()` defaults to zero hops — visible in the current request only.
- Passing `$hops` persists the message across that many additional hops.

```php
$messenger->send('Sticky', MessageLevel::Warning, 2);   // next two requests
$messenger->sendNow('Now + one', MessageLevel::Info, 1); // current + next
```

Passing a hops value below 1 to `send()` throws
`Webware\Message\Exception\InvalidHopsValueException`.

### Level helpers

`danger()`, `info()`, `success()`, and `warning()` default to the current
request (`$now = true` → `sendNow()` with zero hops). Pass `$now = false` to
store for the next request instead:

```php
$messenger->success('Saved!');              // current request only
$messenger->warning('Check this', 1);       // current + next request
$messenger->info('Heads up', 1, false);     // next request only
```

## Reading

```php
$messenger->getMessages();                       // all levels, string lists
$messenger->getMessage(MessageLevel::Success);   // one level, string list
$messenger->hasMessages();                       // bool
```

`getMessage()` accepts an optional `$default` array returned when the level
has no stored messages.

## Lifecycle

- `clearMessages()` removes all stored messages.
- `addHop()` prolongs zero-hop current messages for one more hop.

## Levels

`Webware\Message\MessageLevel` defines `Success`, `Danger`, `Warning`,
`Info`, and `Message` (SSE base type). `Webware\Message\MessageIcon` maps each
level to a bootstrap icon for the view helper.
