<?php

namespace Aesis\SafetyBroadcast\Tests\Fixtures;

use Aesis\SafetyBroadcast\Concerns\SafelyDispatchesBroadcastEvents;

final class SafelyDispatchableEvent
{
    use SafelyDispatchesBroadcastEvents;

    public function __construct(public string $value = 'ok', bool $throw = false)
    {
        if ($throw) {
            throw new \RuntimeException('Dispatch failed.');
        }
    }
}
