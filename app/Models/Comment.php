<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * @property-read User $user
 */
class Comment extends Model
{
    use HasFactory;

    public const ANONYMOUS_NAME = 'Гость';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<string>
     */
    protected $fillable = [
        'user_id',
        'film_id',
        'comment_id',
        'text',
        'rating',
        'is_external',
    ];

    /**
     * Дополнительные вычисляемые атрибуты.
     *
     * @var array
     */
    protected $appends = [
        'author_name',
    ];   
        
    /**
     * Отношение "один ко многим" к модели User.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Отношение "один ко многим" к модели Film.
     *
     * @return BelongsTo
     */
    public function film(): BelongsTo
    {
        return $this->belongsTo(Film::class);
    }

    /**
     * Отношение "многие к одному" к модели Comment (родительский комментарий).
     *
     * @return BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    /**
     * Отношение "один ко многим" к модели Comment (дочерние комментарии).
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(Comment::class, 'comment_id');
    }

    /**
     * Проверяет, имеет ли комментарий дочерние комментарии.
     *
     * @return bool
     */
    public function doesNotHaveChildren(): bool
    {
        return $this->children()->count() === 0;
    }

    /**
     * Получает имя автора комментария.
     *
     * @return string
     */
    protected function getAuthorNameAttribute(): string
    {
        if ($this->is_external) {
            return $this::ANONYMOUS_NAME;
        }
        return $this->user->name;
    }

    /**
     * Получает дату последнего внешнего комментария.
     *
     * @return Carbon|null
     */
    public static function getLastExternalCommentDate(): ?Carbon
    {
        $lastCommentDate = self::where('is_external', true)->max('created_at');

        return $lastCommentDate ? Carbon::parse($lastCommentDate) : null;
    }
}
