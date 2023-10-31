<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenreRequest;
use App\Http\Responses\Base;
use App\Http\Responses\Success;
use App\Models\Genre;

class GenreController extends Controller
{
    /**
     * Получение списка жанров.
     *
     * @return Base
     */
    public function index(): Base
    {
        $genres = Genre::all();
        return new Success($genres);
    }

    /**
     * Редактирование жанра.
     *
     * @return Base
     */
    public function update(GenreRequest $request, Genre $genre): Base
    {
        $genre->update([
            'name' => $request->input('name'),
        ]);
        return new Success($genre);
    }
}
