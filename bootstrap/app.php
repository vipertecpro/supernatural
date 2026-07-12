<?php

use App\Domain\Catalog\Exceptions\InvalidCatalogOperation;
use App\Domain\Editorial\Exceptions\InvalidEditorialOperation;
use App\Domain\Editorial\Exceptions\OptimisticLockConflict;
use App\Domain\Lore\Exceptions\InvalidLoreOperation;
use App\Domain\Media\Exceptions\InvalidMediaOperation;
use App\Domain\Moderation\Exceptions\InvalidModerationOperation;
use App\Domain\UserJourney\Exceptions\InvalidJourneyOperation;
use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\EnforceUserRestrictions;
use App\Http\Middleware\EnsureVerifiedUserAccess;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Support\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['web', 'auth:sanctum', 'verified']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AssignRequestId::class);
        $middleware->alias(['restrictions' => EnforceUserRestrictions::class]);

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureVerifiedUserAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, 'unauthenticated', 'Authentication is required.', 401)
                : null;
        });

        $exceptions->render(function (ValidationException $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error(
                    $request,
                    'validation_failed',
                    'The submitted data is invalid.',
                    422,
                    ['errors' => $exception->errors()],
                )
                : null;
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, 'forbidden', 'You are not authorized to perform this action.', 403)
                : null;
        });

        $exceptions->render(function (InvalidCatalogOperation $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, 'invalid_catalog_transition', $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (OptimisticLockConflict $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, $exception->errorCode, $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (InvalidLoreOperation $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, $exception->errorCode, $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (InvalidMediaOperation $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, $exception->errorCode, $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (InvalidModerationOperation $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, $exception->errorCode, $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (InvalidJourneyOperation $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, $exception->errorCode, $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (InvalidEditorialOperation $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, $exception->errorCode, $exception->getMessage(), 409)
                : null;
        });

        $exceptions->render(function (ThrottleRequestsException $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error(
                    $request,
                    'rate_limited',
                    'Too many requests.',
                    429,
                    headers: $exception->getHeaders(),
                )
                : null;
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, 'not_found', 'The requested resource was not found.', 404)
                : null;
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request): ?JsonResponse {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            $isUnverified = $exception->getStatusCode() === 403
                && $exception->getMessage() === 'Your email address is not verified.';
            $isForbidden = $exception->getStatusCode() === 403 && ! $isUnverified;

            return ApiResponse::error(
                $request,
                match (true) {
                    $isUnverified => 'email_unverified',
                    $isForbidden => 'forbidden',
                    default => 'http_error',
                },
                match (true) {
                    $isUnverified => $exception->getMessage(),
                    $isForbidden => 'You are not authorized to perform this action.',
                    default => 'The request could not be completed.',
                },
                $exception->getStatusCode(),
                headers: $exception->getHeaders(),
            );
        });

        $exceptions->render(function (Throwable $exception, Request $request): ?JsonResponse {
            return $request->is('api/v1/*')
                ? ApiResponse::error($request, 'unexpected_error', 'An unexpected error occurred.', 500)
                : null;
        });
    })->create();
