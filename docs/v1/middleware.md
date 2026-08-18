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
