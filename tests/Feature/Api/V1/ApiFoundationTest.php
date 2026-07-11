<?php

use App\Actions\Authorization\AssignRole;
use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('health endpoint returns only the public response contract', function () {
    $response = $this->withHeader('X-Request-ID', 'api-health-test')
        ->getJson(route('api.v1.health'));

    $response->assertSuccessful()
        ->assertHeader('X-Request-ID', 'api-health-test')
        ->assertExactJson([
            'data' => ['status' => 'ok'],
            'meta' => ['api_version' => 'v1', 'request_id' => 'api-health-test'],
        ]);
});

test('guest me requests use the authentication error contract', function () {
    $this->getJson(route('api.v1.me'))
        ->assertUnauthorized()
        ->assertJsonPath('data', null)
        ->assertJsonPath('error.code', 'unauthenticated')
        ->assertJsonPath('meta.api_version', 'v1');
});

test('unverified users cannot access me', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->getJson(route('api.v1.me'))
        ->assertForbidden()
        ->assertJsonPath('error.code', 'email_unverified');
});

test('verified me response exposes safe identity roles and permissions', function () {
    $user = User::factory()->create();
    app(AssignRole::class)->handle($user, RoleName::Contributor);

    $response = $this->actingAs($user)->getJson(route('api.v1.me'));

    $response->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email_verified', true)
        ->assertJsonPath('data.roles.0', 'contributor')
        ->assertJsonPath('meta.api_version', 'v1')
        ->assertJsonMissingPath('data.password')
        ->assertJsonMissingPath('data.remember_token')
        ->assertJsonFragment(['content.contribute']);
});

test('missing API routes use the not found error contract', function () {
    $this->getJson('/api/v1/missing')
        ->assertNotFound()
        ->assertJsonPath('error.code', 'not_found')
        ->assertJsonPath('meta.api_version', 'v1');
});

test('validation errors use the shared error contract', function () {
    Route::post('/api/v1/test-validation', function (Request $request) {
        return $request->validate(['name' => ['required', 'string']]);
    });

    $this->postJson('/api/v1/test-validation')
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'validation_failed')
        ->assertJsonStructure(['error' => ['details' => ['errors' => ['name']]]]);
});

test('authorization errors use the shared error contract', function () {
    Route::get('/api/v1/test-authorization', function (): never {
        throw new AuthorizationException;
    });

    $this->getJson('/api/v1/test-authorization')
        ->assertForbidden()
        ->assertJsonPath('error.code', 'forbidden');
});

test('unexpected errors do not expose internal details', function () {
    Route::get('/api/v1/test-error', function (): never {
        throw new RuntimeException('sensitive internal detail');
    });

    $this->getJson('/api/v1/test-error')
        ->assertServerError()
        ->assertJsonPath('error.code', 'unexpected_error')
        ->assertJsonMissing(['sensitive internal detail']);
});

test('public API rate limiting uses the error contract', function () {
    config()->set('api.public_rate_limit_per_minute', 2);

    $this->getJson(route('api.v1.health'))->assertSuccessful();
    $this->getJson(route('api.v1.health'))->assertSuccessful();

    $this->getJson(route('api.v1.health'))
        ->assertTooManyRequests()
        ->assertJsonPath('error.code', 'rate_limited');
});
