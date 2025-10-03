<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse|RedirectResponse
    {
        // Always redirect to dashboard after login (regardless of role)
        return $request->wantsJson()
            ? new JsonResponse([], 200)
            : redirect()->route('dashboard');
    }
}
