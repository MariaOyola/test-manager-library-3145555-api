<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\{Exceptions, Middleware};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Library\Adapters\Inbound\Http\{ErrorResponses, ProtectEndpoints};
use Library\Application\Errors\{InvalidCredentials, InvalidToken};
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        then: fn () => Route::middleware('api')->get('/health', static fn () => ['status' => 'ok']),
    )
    ->withCommands([Library\Adapters\Inbound\Console\ProvisionAdminCommand::class])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(ProtectEndpoints::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport([InvalidCredentials::class, InvalidToken::class]);
        $exceptions->shouldRenderJsonWhen(fn (Request $request) => true);
        $exceptions->render(function (Throwable $error, Request $request) {
            if ($error instanceof InvalidCredentials) {
                return ErrorResponses::make('INVALID_CREDENTIALS', $error->getMessage(), 401);
            }
            if ($error instanceof InvalidToken) {
                return ErrorResponses::make('INVALID_CREDENTIALS', 'Se requiere un token válido.', 401);
            }
            if ($error instanceof ValidationException) {
                $details = [];
                foreach (array_keys($error->errors()) as $field) {
                    $details[] = ['field' => $field, 'issue' => 'Campo obligatorio o formato inválido.'];
                }
                return ErrorResponses::make('VALIDATION_ERROR', 'Revisa los campos de la solicitud.', 422, $details);
            }
            $status = $error instanceof HttpExceptionInterface ? $error->getStatusCode() : 500;
            return ErrorResponses::make(
                $status === 404 ? 'NOT_FOUND' : ($status === 403 ? 'FORBIDDEN_ROLE' : 'HTTP_ERROR'),
                $status === 404 ? 'Recurso no encontrado.' : ($status === 403 ? 'No tienes permiso para realizar esta operación.' : 'No se pudo procesar la solicitud.'),
                $status,
            );
        });
    })
    ->create();
