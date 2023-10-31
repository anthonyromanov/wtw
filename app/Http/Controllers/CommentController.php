<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Responses\Base;
use App\Http\Responses\Fail;
use App\Http\Responses\Success;
use App\Models\Comment;
use App\Models\Film;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{
    /**
     * Получение отзывов к фильму.
     *
     * @return Base
     */
    public function index(Film $film): Base
    {
        $comments = $film->comments()->get();
        return new Success($comments);
    }

    /**
     * Добавление отзыва к фильму.
     *
     * @return Base
     */
    public function store(CommentRequest $request, Film $film): Base
    {
        /** @var User|null $user */
        $user = Auth::user();

        $comment = $film->comments()->create([
            'comment_id' => $request->get('comment_id', null),
            'text' => $request->input('text'),
            'rating' => $request->input('rating'),
            'user_id' => $user->id,
        ]);

        $film->calculateRating();

        return new Success($comment);
    }

    /**
     * Редактирование отзыва к фильму.
     *
     * @return Base
     */
    public function update(CommentRequest $request, Comment $comment): Base
    {
        if (Gate::denies('comment-edit', $comment)) {
            return new Fail('Недостаточно прав.', Response::HTTP_FORBIDDEN);
        }

        $comment->update([
            'text' => $request->input('text'),
            'rating' => $request->input('rating'),
        ]);

        $film = $comment->film;
        $film->calculateRating();

        return new Success($comment);
    }

    /**
     * Удаление отзыва к фильму.
     *
     * @return Base
     */
    public function destroy(Request $request, Comment $comment): Base
    {
        if (Gate::denies('comment-delete', $comment)) {
            return new Fail('Недостаточно прав.', Response::HTTP_FORBIDDEN);
        }

        $comment->children()->delete();
        $comment->delete();

        $film = $comment->film;
        $film->calculateRating();

        return new Success(null, Response::HTTP_NO_CONTENT);
    }
}
