<?php

use App\Models\User;

beforeEach(function () {
    config()->set('broadcasting.default', 'pusher');
    config()->set('broadcasting.connections.pusher.key', 'test-key');
    config()->set('broadcasting.connections.pusher.secret', 'test-secret');
    config()->set('broadcasting.connections.pusher.app_id', 'test-app');

    require base_path('routes/channels.php');
});

test('guests cannot authenticate private channels', function () {
    $user = User::factory()->create();

    $this->postJson('/broadcasting/auth', channelPayload($user))
        ->assertUnauthorized();
});

test('unverified users cannot authenticate private channels', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', channelPayload($user))
        ->assertForbidden();
});

test('verified users may authenticate only their own private channel', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', channelPayload($user))
        ->assertSuccessful()
        ->assertJsonStructure(['auth']);

    $this->actingAs($user)
        ->postJson('/broadcasting/auth', channelPayload($otherUser))
        ->assertForbidden();
});

/** @return array{socket_id: string, channel_name: string} */
function channelPayload(User $user): array
{
    return [
        'socket_id' => '1234.5678',
        'channel_name' => 'private-App.Models.User.'.$user->id,
    ];
}
