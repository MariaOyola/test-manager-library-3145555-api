<?php

namespace Library\Adapters\Inbound\Http;

use Closure;
use Illuminate\Http\Request;
use Library\Application\Errors\InvalidToken;
use Library\Application\Ports\Outbound\{TokenVerifier, UserRepository};
use Symfony\Component\HttpFoundation\Response;

final readonly class ProtectEndpoints
{
    public function __construct(private TokenVerifier $tokens, private UserRepository $users) {}

    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.trim($request->path(), '/');
        if (($request->isMethod('GET') && in_array($path, ['/health', '/api/v1/health'], true))
            || ($request->isMethod('POST') && $path === '/api/v1/auth/login')) {
            return $next($request);
        }

        $token = $request->bearerToken();
        if (!$token) {
            throw new InvalidToken();
        }
        $identity = $this->tokens->verify($token);
        $user = $this->users->findById($identity->userId);
        if (!$user || $user->role !== $identity->role) {
            throw new InvalidToken();
        }
        // Política del contrato: se evalúa antes de resolver un recurso.
        $adminOnly = ($path === '/api/v1/users' && $request->isMethod('POST'))
            || $path === '/api/v1/reports/most-borrowed'
            || (preg_match('#^/api/v1/books(?:/|$)#', $path) && in_array($request->method(), ['POST', 'PUT', 'DELETE'], true));
        if ($adminOnly && $identity->role !== 'admin') {
            return ErrorResponses::make('FORBIDDEN_ROLE', 'No tienes permiso para realizar esta operación.', 403);
        }
        $request->attributes->set('identity', $identity);
        return $next($request);
    }
}
