# Middleware

`Webware\Message\Middleware\MessageMiddleware` builds the per-request
`SystemMessenger` from the session, injects it into the view helper, and
exposes it as a request attribute.

## Wiring

The Mezzio session middleware must run first so the session attribute exists:

```php
// config/pipeline.php
$app->pipe(\Mezzio\Session\SessionMiddleware::class);
$app->pipe(\Webware\Message\Middleware\MessageMiddleware::class);
```

The middleware is registered by `ConfigProvider`; the factory resolves the
`SystemMessenger` view helper from the application's view
`HelperPluginManager` (provided by the laminas-view renderer).

## Behavior

For each request, the middleware:

1. Reads `SessionMiddleware::SESSION_ATTRIBUTE` from the request.
2. Throws `Webware\Message\Exception\MissingSessionException` when the
   attribute is missing or not a `Mezzio\Session\SessionInterface`.
3. Constructs `SystemMessenger` for the session, preparing any stored
   messages (decrementing hops, expiring zero-hop entries).
4. Calls `setMessenger()` on the view helper so templates render the current
   request's messages.
5. Delegates to the next handler with the messenger attached under the
   `SystemMessengerInterface::class` attribute key.

Downstream middleware and handlers can read the messenger:

```php
use Webware\Message\SystemMessengerInterface;

$messenger = $request->getAttribute(SystemMessengerInterface::class);
```

## Notification middleware

`Webware\Message\Middleware\NotificationMiddleware` turns a completed command
result into a flash notification. It reads the message-bus `CommandResult`
request attribute and pushes a success or warning message when the dispatched
command implements `NotificationCapableInterface`.

### Wiring

The middleware must run after `MessageMiddleware` (so the
`SystemMessengerInterface` request attribute exists) and after the middleware
that dispatches the command and stores the `CommandResult` attribute:

```php
// config/pipeline.php
$app->pipe(\Mezzio\Session\SessionMiddleware::class);
$app->pipe(\Webware\Message\Middleware\MessageMiddleware::class);
// ... routing and command-processing middleware ...
$app->pipe(\Webware\Message\Middleware\NotificationMiddleware::class);
```

`NotificationMiddleware` is registered by `ConfigProvider` as an invokable and
requires no constructor dependencies.

### Behavior

For each request, the middleware:

1. Reads the `CommandResult` and `SystemMessengerInterface` request attributes.
2. Returns immediately when either attribute is missing.
3. Reads the dispatched command via `CommandResult::getCommand()`.
4. Returns immediately when the command does not implement
   `NotificationCapableInterface`.
5. Maps the result status to a flash message:
   - `MessageStatus::Success` → `$successMessage` (success level)
   - `MessageStatus::Failure` → `$failureMessage` (warning level)
6. Delegates to the next handler.

### NotificationCapableInterface

Commands that want a flash notification declare their user-facing text by
implementing `NotificationCapableInterface`:

```php
use Webware\Message\NotificationCapableInterface;
use Webware\MessageBus\Command\CommandInterface;

final readonly class SaveRoleCommand implements CommandInterface, NotificationCapableInterface
{
    public string $successMessage { get => 'Role saved.'; }

    public string $failureMessage { get => 'Role could not be saved.'; }
}
```
