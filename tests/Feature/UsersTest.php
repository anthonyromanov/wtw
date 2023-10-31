<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Тестирование получения данных пользователя.
     */
    public function testShowUser(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/api/user');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                    'avatar',
                    'role',
                ],
            ],
        ]);
        $response->assertJson([
            'data' => [
                'user' => $user->toArray(),
            ],
        ]);
    }

    /**
     * Тестирование обновления данных пользователя гостем.
     */
    public function testUpdateUserDataByGuest(): void
    {
        $newName = 'Новое имя';
        $newEmail = 'newmail@gmail.com';

        $response = $this->patchJson('/api/user', [
            'name' => $newName,
            'email' => $newEmail,
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => 'Запрос требует аутентификации.',
        ]);
    }

    /**
     * Тестирование обновления данных пользователя.
     */
    public function testUpdateUserData(): void
    {
        $user = User::factory()->create();
        $newName = 'Новое имя';
        $newEmail = 'newmail@gmail.com';

        $response = $this->actingAs($user)->patchJson('/api/user', [
            'name' => $newName,
            'email' => $newEmail,
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'data' => [
                'user' => [
                    'name' => $newName,
                    'email' => $newEmail,
                ],
            ],
        ]);
    }

    /**
     * Тестирование изменения пароля пользователя.
     */
    public function testUpdatePasswordChanges(): void
    {
        $user = User::factory()->create();
        $newPassword = 'jamesbond007';

        $response = $this->actingAs($user)->patchJson('/api/user', [
            'name' => $user->name,
            'email' => $user->email,
            'password' => $newPassword,
        ]);

        $user->refresh();

        $response->assertStatus(Response::HTTP_OK);
        $this->assertTrue(Hash::check($newPassword, $user->password));
    }

    /**
     * Тестирование изменения аватара пользователя.
     */
    public function testUpdateAvatarChanges(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $avatar = UploadedFile::fake()->image('avatar.jpg');

        $response = $this->actingAs($user)->patchJson('/api/user', [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $avatar,
        ]);

        $user->refresh();

        $response->assertStatus(Response::HTTP_OK);

        $this->assertEquals(config('custom.paths.avatar') . $avatar->hashName(), $user->avatar);
        Storage::disk('local')->assertExists(config('custom.paths.avatar') . $avatar->hashName());
    }

    /**
     * Тестирование ошибок валидации при обновлении данных пользователя.
     */
    public function testUpdateValidationError(): void
    {
        $user = User::factory()->create();
        $differentUser = User::factory()->create();

        $data = [
            'email' => $differentUser->email,
            'password' => 'short',
            'name' => '',
            'avatar' => UploadedFile::fake()->create('document.pdf', 20480),
        ];

        $response = $this->actingAs($user)->patchJson("/api/user", $data);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'email',
            'password',
            'name',
            'avatar',
        ]);
    }
}
