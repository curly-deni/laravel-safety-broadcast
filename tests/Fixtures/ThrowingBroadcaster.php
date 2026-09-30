<?php

namespace Aesis\SafetyBroadcast\Tests\Fixtures;

use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Contracts\Broadcasting\Broadcaster;

final class ThrowingBroadcaster implements Broadcaster
{
    public function auth($request) {}

    public function validAuthenticationResponse($request, $result) {}

    public function broadcast(array $channels, $event, array $payload = []): void
    {
        throw new BroadcastException('Broadcast failed.');
    }
}
