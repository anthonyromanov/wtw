<?php

namespace App\Http\Requests;

use App\Models\Film;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFilmRequest extends FormRequest
{
    /**
     * Определяет, авторизован ли пользователь для выполнения запроса.
     *
     * @return bool Разрешение на выполнение запроса.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Возвращает правила валидации для запроса.
     *
     * @return array Правила валидации.
     */
    public function rules()
    {
        return [
            'imdb_id' => [
                'required',
                'string',
                Rule::unique(Film::class),
                'regex:/^tt\d{7,}$/i',
            ],
        ];
    }
}
