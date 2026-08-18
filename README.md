# webware/webware-message

[![PHP Version](https://img.shields.io/packagist/php-v/webware/webware-message)](https://packagist.org/packages/webware/webware-message)
[![Latest Version](https://img.shields.io/packagist/v/webware/webware-message)](https://packagist.org/packages/webware/webware-message)
[![License](https://img.shields.io/github/license/webinertia/webware-message)](LICENSE)
[![Continuous Integration](https://github.com/webinertia/webware-message/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/webinertia/webware-message/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/webinertia/webware-message/graph/badge.svg)](https://codecov.io/gh/webinertia/webware-message)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fwebinertia%2Fwebware-message%2F1.0.x)](https://dashboard.stryker-mutator.io/reports/github.com/webinertia/webware-message/1.0.x)

A sessions-backed flash messenger for [Mezzio](https://docs.mezzio.dev/)
applications. Provides PSR-15 middleware, a `SystemMessengerInterface`
service, and a view helper for rendering bootstrap-style toast messages.

## Documentation

- [Installation](docs/v1/installation.md)
- [Configuration Reference](docs/v1/configuration.md)
- [Messenger](docs/v1/messenger.md)
- [Middleware](docs/v1/middleware.md)
- [View Helper](docs/v1/view-helper.md)

## Quick Start

```bash
composer require webware/webware-message
```

`laminas/laminas-component-installer` injects
`Webware\Message\ConfigProvider` automatically.

### Middleware

Register `Webware\Message\Middleware\MessageMiddleware` in the route pipeline
(after the session middleware):

```php
// config/pipeline.php
$app->pipe(Mezzio\Session\SessionMiddleware::class);
$app->pipe(Webware\Message\Middleware\MessageMiddleware::class);
```

### Sending messages

The middleware exposes a `Webware\Message\SystemMessenger` instance as the
request attribute `SystemMessengerInterface::class`:

```php
use Webware\Message\MessageLevel;
use Webware\Message\SystemMessengerInterface;

/** @var SystemMessengerInterface $messenger */
$messenger = $request->getAttribute(SystemMessengerInterface::class);

$messenger->success('Saved!');          // current request only
$messenger->warning('Check this', 1);   // current + next request
$messenger->info('Heads up', 1, false); // next request only
```

Hops semantics: `send()` defaults to one hop (next request), `sendNow()`
defaults to zero hops (current request only), and the level helpers
(`success`, `danger`, `info`, `warning`) default to the current request.

### Rendering

The `systemMessenger` view helper renders bootstrap-style toasts for all
stored levels:

```php
<?= $this->systemMessenger() ?>
```

Available levels are `MessageLevel::Success`, `MessageLevel::Danger`,
`MessageLevel::Warning`, `MessageLevel::Info`, and `MessageLevel::Message`
(SSE base type).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md).
