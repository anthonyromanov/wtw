<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\PromoController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SimilarController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/* Регистрация */
Route::post('/register', [RegisterController::class, 'register']);

/* Авторизация */
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

/* Пользователь */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::patch('/user', [UserController::class, 'update']);
});

/* Фильмы */
Route::get('/films', [FilmController::class, 'index']);
Route::get('/films/{film}', [FilmController::class, 'show']);
Route::middleware(['auth:sanctum', 'moderator'])->group(function () {
    Route::post('/films', [FilmController::class, 'store']);
    Route::patch('/films/{film}', [FilmController::class, 'update']);
});

/* Жанры */
Route::get('/genres', [GenreController::class, 'index']);
Route::middleware(['auth:sanctum', 'moderator'])->patch('/genres/{genre}', [GenreController::class, 'update']);

/* Избранное */
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/favorite', [FavoriteController::class, 'index']);
    Route::post('/films/{film}/favorite/', [FavoriteController::class, 'store']);
    Route::delete('/films/{film}/favorite/', [FavoriteController::class, 'destroy']);
});

/* Похожие фильмы */
Route::get('/films/{film}/similar', [SimilarController::class, 'index']);

/* Комментарии */
Route::get('/films/{film}/comments', [CommentController::class, 'index']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/films/{film}/comments', [CommentController::class, 'store']);
    Route::patch('/comments/{comment}', [CommentController::class, 'update']);
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy']);
});

/* Промо */
Route::get('/promo', [PromoController::class, 'index']);
Route::middleware(['auth:sanctum', 'moderator'])->post('/promo/{film}', [PromoController::class, 'store']);
