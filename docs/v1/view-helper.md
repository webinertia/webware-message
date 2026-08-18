# View Helper

`Webware\Message\View\Helper\SystemMessenger` renders bootstrap-style toast
markup for every stored message level.

## Usage

```php
<?= $this->systemMessenger() ?>
```

Registered aliases: `systemMessenger`, `messenger`, and `systemMessage`.

The helper renders one toast per level that has stored messages; multiple
messages within a level are joined with `<br>`. Output is empty when no
messenger has been injected (e.g. the middleware did not run).

```html
<div class="toast" role="alert" data-bs-autohide="false" aria-live="assertive" aria-atomic="true">
    <div class="toast-header bg-success-subtle">
        <i class="text-success bi bi-check-circle"></i>
        <strong class="ms-auto me-auto">Success</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
    <div class="toast-body">
        Saved!
    </div>
</div>
```

Icons come from `Webware\Message\MessageIcon`; styling targets
[Bootstrap 5](https://getbootstrap.com/) with bootstrap-icons classes.

## Wiring

The helper is registered through `ConfigProvider` and resolved from the
application's view `HelperPluginManager`. The middleware injects the
per-request `SystemMessenger` via `setMessenger()`; the helper returns an
empty string until that happens.
