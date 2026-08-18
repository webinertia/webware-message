# Configuration Reference

`Webware\Message\ConfigProvider` registers four sections.

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

## Message Templates

```php
// getMessageTemplates()
[
    SystemMessengerInterface::MESSAGE_TEMPLATES => [
        // YourCommand::class => [
        //     NotificationCapableInterface::MESSAGE_SUCCESS => 'Your success message',
        //     NotificationCapableInterface::MESSAGE_FAILURE => 'Your failure message',
        // ],
    ],
]
```

Applications may map command classes to success/failure template strings,
keyed under `SystemMessengerInterface::MESSAGE_TEMPLATES` (`message_templates`).
