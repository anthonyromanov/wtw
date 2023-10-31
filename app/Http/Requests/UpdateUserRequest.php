<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
     *
     * @return bool Разрешение на выполнение запроса.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Возвращает правила валидации для запроса.
     *
     * @return array Правила валидации.
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|unique:users,email,' . $this->user()->id,
            'password' => 'nullable|string|min:8',
            'name' => 'required|string|max:255',
            'avatar' => 'nullable|file|image|max:10240',
        ];
    }
}
