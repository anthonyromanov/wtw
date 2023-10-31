<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenreRequest extends FormRequest
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
     * Возвращает атрибуты для сообщений об ошибках валидации.
     *
     * @return array Атрибуты для сообщений об ошибках.
     */
    public function attributes(): array
    {
        return [
            'name' => 'Название',
        ];
    }

    /**
     * Возвращает правила валидации для запроса.
     *
     * @return array Правила валидации.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:255',
        ];
    }

    /**
     * Возвращает пользовательские сообщения об ошибках валидации.
     *
     * @return array Сообщения об ошибках.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Поле Название обязательно для заполнения.',
        ];
    }
}
