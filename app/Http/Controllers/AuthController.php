<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Responses\Base;
use App\Http\Responses\Fail;
use App\Http\Responses\Success;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * @psalm-api
 */
class AuthController extends Controller
{
    /**
     * Выполняет авторизацию пользователя в сервисе.
     * @param LoginRequest $request
     *
     * @return Base
     */
    public function login(LoginRequest $request): Base
    {
        if (!Auth::statefulGuard('user')->attempt($request->validated())) {
            return new Fail(trans('auth.failed'), Response::HTTP_UNAUTHORIZED);
        }

        /** @var User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return new Success([
            'token' => $token,
        ]);
    }

    /**
     * Выполняет выход пользователя из сервиса.
     *
     * @return Base
     */
    public function logout(): Base
    {
        /** @var User $user */
        $user = Auth::user();
        $user->tokens()->delete();

        return new Success(null, Response::HTTP_NO_CONTENT);
    }
}
