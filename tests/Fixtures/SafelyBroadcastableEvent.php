<?php

namespace Aesis\SafetyBroadcast\Tests\Fixtures;

use Aesis\SafetyBroadcast\Concerns\SafelyDispatchesBroadcastEvents;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

final class SafelyBroadcastableEvent implements ShouldBroadcastNow
{
    use SafelyDispatchesBroadcastEvents;

    public function __construct(public string $value = 'ok') {}

    public function broadcastOn(): array
    {
        return [new Channel('safety-broadcast-tests')];
    }

    public function broadcastAs(): string
    {
        return 'safety-broadcast-test';
    }

    public function broadcastWith(): array
    {
        return ['value' => $this->value];
    }
}
