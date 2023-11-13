<?php

namespace App\Exceptions;

use App\Http\Responses\Fail;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * @psalm-api
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $_e) {
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        return $request->expectsJson()
        ? response()->json([
            'message' => 'Запрос требует аутентификации.',
        ], Response::HTTP_UNAUTHORIZED)
        : redirect()->guest($exception->redirectTo() ?? route('login'));
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            if ($e instanceof NotFoundHttpException || $e instanceof ModelNotFoundException) {
                return response()->json([
                    'message' => 'Запрашиваемая страница не существует.',
                ], Response::HTTP_NOT_FOUND);
            }

            if ($e instanceof ValidationException) {
                $errors = $e->errors();
                $response = [
                    'message' => 'Переданные данные не корректны.',
                    'errors' => $errors,
                ];
                return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        return parent::render($request, $e);
    }

    protected function prepareJsonResponse($request, Throwable $e): JsonResponse
    {
        return (new Fail($e->getMessage(), (int)$e->getCode() ?: Response::HTTP_INTERNAL_SERVER_ERROR, $e))
            ->toResponse($request);
    }
}
