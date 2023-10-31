<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model implements Authenticatable
{
    use \Illuminate\Auth\Authenticatable;
    use HasFactory;
    use Notifiable;
    use HasApiTokens;

    public const ROLE_USER = 'user';
    public const ROLE_MODERATOR = 'moderator';

    /**
     * Атрибуты, которые можно массово присваивать.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role',
    ];

    /**
     * Скрытые атрибуты.
     *
     * @var array<int,string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Показывает фильмы, которые пользователь добавил в Избранное.
     *
     * @return BelongsToMany
     */
    public function favoriteFilms(): BelongsToMany
    {
        return $this->belongsToMany(Film::class, 'user_favorites');
    }

    /**
     * Показывает комментарии пользователя
     *
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Проверяет, является ли пользователь модератором.
     *
     * @return bool
     */
    public function isModerator(): bool
    {
        return $this->role === self::ROLE_MODERATOR;
    }

    /**
     * Проверяет добавлен ли фильм в Изранное у пользователя
     *
     * @return bool
     */
    public function hasFavorite($filmId): bool
    {
        return $this->favoriteFilms()->where('film_id', $filmId)->exists();
    }
}
