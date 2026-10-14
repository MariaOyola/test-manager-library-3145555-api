<?php

use Illuminate\Support\Facades\Route;
use Library\Adapters\Inbound\Http\LoginController;

Route::post('/auth/login', LoginController::class);
Route::get('/health', static fn (): array => ['status' => 'ok']);
// Las operaciones de HU-02 a HU-10 se incorporarán con sus casos de uso.
// ProtectEndpoints protege también rutas inexistentes y futuras.
