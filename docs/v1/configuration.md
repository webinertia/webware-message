# Configuration Reference

`Webware\Message\ConfigProvider` registers three sections.

## Dependencies

```php
// getDependencies()
[
    'aliases' => [
        SystemMessengerInterface::class => SystemMessenger::class,
    ],
    'factories' => [
        MessageMiddleware::class => MessageMiddlewareFactory::class,
    ],
    'invokables' => [
        NotificationMiddleware::class => NotificationMiddleware::class,
    ],
]
```

`SystemMessenger` is not a singleton — it is constructed per request from the
session by `MessageMiddleware`.

## View Helpers

```php
// getViewHelpers()
[
    'aliases' => [
        'messenger'       => View\Helper\SystemMessenger::class,
        'systemMessage'   => View\Helper\SystemMessenger::class,
        'systemMessenger' => View\Helper\SystemMessenger::class,
    ],
    'factories' => [
        View\Helper\SystemMessenger::class => View\Helper\SystemMessengerFactory::class,
    ],
]
```

The helper is resolved through the application's view `HelperPluginManager`;
`laminas/laminas-view` is listed in `suggest` and is required when rendering
through the helper.

## Templates

```php
// getTemplates()
[
    'paths' => [
        'message' => [__DIR__ . '/../templates/'],
    ],
]
```
