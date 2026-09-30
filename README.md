# Laravel Safety Broadcast

Safely dispatch Laravel broadcast events synchronously. Exceptions raised during dispatch are reported and logged, and the dispatch call returns `null` when one occurs.

## Requirements

- PHP 8.4 or later
- Laravel 11, 12, or 13

## Installation

```bash
composer require curly-deni/laravel-safety-broadcast
```

The package does not need a service provider, configuration, or database migrations.

## Usage

Add the trait to a broadcast event in place of Laravel's `Dispatchable` trait:

```php
<?php

namespace App\Events;

use Aesis\SafetyBroadcast\Concerns\SafelyDispatchesBroadcastEvents;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class OrderUpdated implements ShouldBroadcastNow
{
    use SafelyDispatchesBroadcastEvents;

    public function __construct(public int $orderId) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('orders.'.$this->orderId)];
    }
}
```

Dispatch events as usual:

```php
OrderUpdated::dispatch($order->id);
OrderUpdated::dispatchIf($shouldBroadcast, $order->id);
OrderUpdated::dispatchUnless($isMuted, $order->id);
```

For `ShouldBroadcastNow` events, Laravel broadcasts during the dispatch call. If event construction, a synchronous listener, or the broadcaster throws, the exception is reported and logged with the event class; it does not escape from `dispatch`, which returns `null`.

For `ShouldBroadcast` events, Laravel queues the broadcast job. This trait can suppress an exception raised while dispatching the event or scheduling that job, but it cannot catch a broadcast failure that happens later in a queue worker. Use `ShouldBroadcastNow` when the broadcast itself must happen inside this protected call.

The catch covers the full synchronous Laravel event-dispatch call. That means exceptions from other synchronous listeners for the same event are also reported and suppressed.

Conditional helpers keep Laravel's usual behavior and only dispatch when their condition allows it.

## Testing and code style

```bash
composer test
composer run analyse
composer run format
```

## License

This package is open-sourced software licensed under the [MIT license](LICENSE.md).
