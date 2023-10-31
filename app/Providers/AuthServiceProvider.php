<?php
namespace App\Providers;

use App\Models\User;
use App\Models\Film;
use App\Models\Comment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
        Gate::define('comment-delete', function (User $user, Comment $comment) {
            if ($user->isModerator()) {
                return true;
            }
            return $user->id === $comment->user_id && $comment->doesNotHaveChildren();
        });
        Gate::define('comment-edit', function (User $user, Comment $comment) {
            if ($user->isModerator()) {
                return true;
            }

            return $user->id === $comment->user_id;
        });

        Gate::define('view-films-with-status', function (?User $user, string $status) {
            if ($user && $user->isModerator()) {
                return true;
            }

            return $status === Film::STATUS_READY;
        });
    }
}
