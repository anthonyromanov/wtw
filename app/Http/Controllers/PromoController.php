<?php

namespace App\Http\Controllers;

use App\Http\Responses\Base;
use App\Http\Responses\Success;
use App\Models\Film;
use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    /**
     * Получение текущего промо ролика.
     *
     * @return Base
     */
    public function index(): Base
    {
        $promo = Promo::latest()->first();
        return new Success($promo);
    }

    /**
     * Установка нового промо ролика.
     *
     * @return Base
     */
    public function store(Request $request, Film $film): Base
    {
        $promo = Promo::create(['film_id' => $film->id]);
        return new Success($promo);
    }
}
