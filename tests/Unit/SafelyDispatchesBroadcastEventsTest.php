<?php

use Aesis\SafetyBroadcast\Tests\Fixtures\SafelyBroadcastableEvent;
use Aesis\SafetyBroadcast\Tests\Fixtures\SafelyDispatchableEvent;
use Aesis\SafetyBroadcast\Tests\Fixtures\ThrowingBroadcaster;
use Aesis\SafetyBroadcast\Tests\TestCase;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

uses(TestCase::class);

test('it dispatches the event normally', function (): void {
    Event::fake();

    $event = SafelyDispatchableEvent::dispatch('payload');

    expect($event)->toBeNull();
    Event::assertDispatched(SafelyDispatchableEvent::class);
});

test('it supports conditional dispatch helpers', function (): void {
    Event::fake();

    SafelyDispatchableEvent::dispatchIf(false, 'if-false');
    SafelyDispatchableEvent::dispatchUnless(true, 'unless-true');
    SafelyDispatchableEvent::dispatchIf(true, 'if-true');
    SafelyDispatchableEvent::dispatchUnless(false, 'unless-false');

    Event::assertDispatchedTimes(SafelyDispatchableEvent::class, 2);
    Event::assertDispatched(fn (SafelyDispatchableEvent $event): bool => $event->value === 'if-true');
    Event::assertDispatched(fn (SafelyDispatchableEvent $event): bool => $event->value === 'unless-false');
});

test('it reports and logs dispatch failures without throwing', function (): void {
    $exceptionHandler = Mockery::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')
        ->once()
        ->with(Mockery::type(RuntimeException::class));
    app()->instance(ExceptionHandler::class, $exceptionHandler);

    Log::shouldReceive('error')->zeroOrMoreTimes();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Broadcast event was not dispatched.'
            && $context['event'] === SafelyDispatchableEvent::class
            && $context['exception'] instanceof RuntimeException);

    expect(SafelyDispatchableEvent::dispatch('payload', true))->toBeNull();
});

test('it reports and suppresses synchronous broadcaster failures', function (): void {
    $exceptionHandler = Mockery::mock(ExceptionHandler::class);
    $exceptionHandler->shouldReceive('report')
        ->once()
        ->with(Mockery::type(BroadcastException::class));
    app()->instance(ExceptionHandler::class, $exceptionHandler);

    $broadcastManager = app(BroadcastManager::class);
    $broadcastManager->extend('throwing', fn (): ThrowingBroadcaster => new ThrowingBroadcaster);
    config()->set('broadcasting.default', 'throwing');
    config()->set('broadcasting.connections.throwing', ['driver' => 'throwing']);

    Log::shouldReceive('error')->zeroOrMoreTimes();
    Log::shouldReceive('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'Broadcast event was not dispatched.'
            && $context['event'] === SafelyBroadcastableEvent::class
            && $context['exception'] instanceof BroadcastException);

    expect(SafelyBroadcastableEvent::dispatch('payload'))->toBeNull();
});
