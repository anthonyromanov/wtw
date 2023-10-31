<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserRequest;
use App\Http\Responses\Base;
use App\Http\Responses\Success;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Получение данных о пользователе.
     *
     * @return Base
     */
    public function show(): Base
    {
        $user = Auth::user();
        return new Success([
            'user' => $user,
        ]);
    }

    /**
     * Обновление данных о пользователе.
     *
     * @return Base
     */
    public function update(UpdateUserRequest $request): Base
    {
        /** @var User|null $user */
        $user = Auth::user();
        $data = [
            'email' => $request->input('email'),
            'name' => $request->input('name'),
        ];

        if ($request->has('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $oldAvatar = null;
        if ($request->hasFile('avatar')) {
            $newAvatar = $request->file('avatar');
            $oldAvatar = $user->avatar;
            $filename = $newAvatar->store('public/avatars', 'local');
            $data['avatar'] = $filename;
        }

        $user->update($data);

        if ($oldAvatar) {
            Storage::delete($oldAvatar);
        }

        return new Success([
            'user' => $user,
        ]);
    }
}
