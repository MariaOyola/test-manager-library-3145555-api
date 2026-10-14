<?php

namespace Library\Adapters\Inbound\Http;

use Illuminate\Http\{JsonResponse, Request};
use Library\Application\Services\LoginService;

final readonly class LoginController
{
    public function __construct(private LoginService $login) {}

    public function __invoke(Request $request): JsonResponse
    {
        $data = $request->validate(['username' => ['required', 'string', 'max:120'], 'password' => ['required', 'string']]);
        return response()->json($this->login->execute($data['username'], $data['password']));
    }
}
