<?php

namespace App\Http\Responses;

use App\Domain\Onboarding\OnboardingStateResolver;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\VerifyEmailResponse;
use Symfony\Component\HttpFoundation\Response;

class OnboardingVerifyEmailResponse implements VerifyEmailResponse
{
    public function __construct(private readonly OnboardingStateResolver $states) {}

    public function toResponse($request): Response
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        $destination = $this->states->destination($request->user());
        if ($destination->getTargetUrl() === route('dashboard')) {
            return redirect()->to(route('dashboard', ['verified' => 1]));
        }

        return $destination;
    }
}
