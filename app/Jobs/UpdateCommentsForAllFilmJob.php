<?php

namespace App\Jobs;

use App\Models\Film;
use App\Services\MovieFinder\AcademyRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCommentsForAllFilmJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Обработка задачи по обновлению комментариев для всех фильмов.
     *
     * @return void
     */
    public function handle(AcademyRepository $repository)
    {
        $newComments = $repository->getNewComments();

        $filmsWithNewComments = Film::whereIn('imdb_id', array_column($newComments, 'imdb_id'))->get();

        $filmsWithNewComments->each(function ($film) use ($newComments) {
            $commentsForThisFilm = array_filter($newComments, function ($comment) use ($film) {
                return $comment['imdb_id'] == $film->imdb_id;
            });

            UpdateCommentsForFilmJob::dispatch($film, $commentsForThisFilm);
        });
    }
}