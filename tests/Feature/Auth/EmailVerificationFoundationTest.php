<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

test('registration creates an unverified user', function () {
    Notification::fake();

    $this->post(route('register.store'), [
        'name' => 'New Fan',
        'email' => 'new-fan@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'new-fan@example.com')->firstOrFail();

    expect($user->hasVerifiedEmail())->toBeFalse();
    Notification::assertSentTo($user, VerifyEmail::class);
});

test('unverified users are redirected away from protected web routes', function (string $routeName) {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route($routeName))
        ->assertRedirect(route('verification.notice'));
})->with(['dashboard', 'profile.edit', 'security.edit']);

test('unverified users receive the API verification error contract', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->getJson(route('api.v1.me'))
        ->assertForbidden()
        ->assertJsonPath('error.code', 'email_unverified')
        ->assertJsonPath('meta.api_version', 'v1');
});

test('unsigned verification links are rejected', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.verify', ['id' => $user->id, 'hash' => sha1($user->email)]))
        ->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('expired verification links are rejected', function () {
    $user = User::factory()->unverified()->create();
    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->subMinute(),
        ['id' => $user->id, 'hash' => sha1($user->email)],
    );

    $this->actingAs($user)->get($verificationUrl)->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

test('verification resend is rate limited without sending real email', function () {
    Notification::fake();
    $user = User::factory()->unverified()->create();

    foreach (range(1, 6) as $attempt) {
        $this->actingAs($user)
            ->postJson(route('verification.send'))
            ->assertAccepted();
    }

    $this->actingAs($user)
        ->postJson(route('verification.send'))
        ->assertTooManyRequests();

    Notification::assertSentToTimes($user, VerifyEmail::class, 6);
});

test('verified users can access protected routes', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('dashboard'))->assertSuccessful();
});
