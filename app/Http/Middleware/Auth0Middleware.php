<?php

namespace App\Http\Middleware;

use App\Exceptions\InsufficientScopeException;
use Auth0\SDK\Auth0;
use Auth0\SDK\Exception\InvalidTokenException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class Auth0Middleware {

    public function handle(
        Request $request,
        Closure $next,
        ?string $requiredScope = null
    ) {

        $token = $request->bearerToken();
        if (!$token) {
            return response()
                ->json('No token provided', Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->validateToken($token, $requiredScope);
        } catch (InsufficientScopeException | InvalidTokenException $exception) {
            return response()
                ->json($exception->getMessage(), $exception->getCode());
        }

        return $next($request);
    }

    public function validateToken($token, ?string $requiredScope) {

        $auth0 = new Auth0(
            [
                'domain' => env('AUTH0_DOMAIN'),
                'strategy' => 'api',
                'audience' => [
                    env('AUTH0_AUD'),
                ],
            ]
        );
        $decodedToken = $auth0->decode($token);
        if (!empty($requiredScope)) {
            $this->ensureTokenHasScope($decodedToken->toArray(), $requiredScope);
        }
    }

    private function ensureTokenHasScope(array $decodedToken, string $requiredScope) {

        $tokenScope = $decodedToken['scope'] ?? '';
        if (empty($tokenScope) || !$this->tokenHasScope($tokenScope, $requiredScope)) {

            throw new InsufficientScopeException;
        }

    }

    private function tokenHasScope(string $scopeString, string $requiredScope)
    : bool {

        $tokenScopes = explode(' ', $scopeString);

        return in_array($requiredScope, $tokenScopes);
    }
}
