<?php

namespace Aesis\SafetyBroadcast\Concerns;

use Illuminate\Foundation\Events\Dispatchable;
use Throwable;

trait SafelyDispatchesBroadcastEvents
{
    use Dispatchable {
        dispatch as private dispatchThroughDispatcher;
    }

    public static function dispatch(...$arguments): mixed
    {
        try {
            return self::dispatchThroughDispatcher(...$arguments);
        } catch (Throwable $exception) {
            report($exception);

            logger()->warning('Broadcast event was not dispatched.', [
                'event' => static::class,
                'exception' => $exception,
            ]);

            return null;
        }
    }

    public static function dispatchIf($boolean, ...$arguments): mixed
    {
        return $boolean ? static::dispatch(...$arguments) : null;
    }

    public static function dispatchUnless($boolean, ...$arguments): mixed
    {
        return $boolean ? null : static::dispatch(...$arguments);
    }
}
