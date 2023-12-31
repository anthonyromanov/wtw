<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
     * @psalm-api
     * @return bool Разрешение на выполнение запроса.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Возвращает правила валидации для запроса.
     * @psalm-api
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
