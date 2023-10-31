<?php

namespace App\Jobs;

use App\Models\Comment;
use App\Models\Film;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCommentsForFilmJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Конструктор класса UpdateCommentsForFilmJob.
     */
    public function __construct(protected Film $film, protected array $newComments)
    {
    }

    /**
     * Обработка задачи по обновлению комментариев для фильма.
     *
     * @return void
     */
    public function handle(): void
    {
        foreach ($this->newComments as $commentData) {
            $commentDate = Carbon::parse($commentData['date']);

            Comment::create([
                'film_id' => $this->film->id,
                'text' => $commentData['text'],
                'created_at' => $commentDate,
                'is_external' => true,
            ]);
        }
    }
}