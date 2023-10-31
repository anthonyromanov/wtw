<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Responses\Base;
use App\Http\Responses\Success;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Выполняет регистрацию пользователя в сервисе.
     *
     * @param RegisterRequest $request.
     * @return Base
     */
    public function register(RegisterRequest $request): Base
    {
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            $filename = $avatar->store('public/avatars', 'local');
            $data['avatar'] = $filename;
        }

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $token = $user->createToken('auth_token')->plainTextToken;

        return new Success(['user' => $user, 'token' => $token]);
    }
}
