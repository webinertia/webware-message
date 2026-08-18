# Installation

## Requirements

- PHP 8.4.1 or later
- A Mezzio application (PSR-11 container, PSR-15 middleware)
- Session middleware in the pipeline — the message middleware reads the
  session from `SessionMiddleware::SESSION_ATTRIBUTE`

## Composer

```bash
composer require webware/webware-message
```

`laminas/laminas-component-installer` will prompt you to inject
`Webware\Message\ConfigProvider` into your application's config aggregator.
Accept the prompt or add it manually:

```php
// config/config.php
new Webware\Message\ConfigProvider(),
```

## Sessions

Messages are stored in the session, so the
[Mezzio session middleware](https://docs.mezzio.dev/mezzio-session/) must run
before `MessageMiddleware` in the pipeline. The package requires
`mezzio/mezzio-session` and ships with the native ext-session persistence
(`mezzio/mezzio-session-ext`); any Mezzio session persistence implementation
works.

See [Middleware](middleware.md) for pipeline wiring.
