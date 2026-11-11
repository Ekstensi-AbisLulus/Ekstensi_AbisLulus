<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    /**
     * @return array<string, string>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Siti Nurhaliza',
            'email' => 'siti@example.com',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $overrides);
    }

    public function test_register_page_renders_without_role_field(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Daftar dulu yuk!')
            ->assertSee('Mari bergabung dengan AbisLulus!')
            ->assertSee('name="name"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertDontSee('name="role"', false)
            ->assertDontSee('Pilih Role');
    }

    public function test_user_can_register_and_is_redirected_to_profile(): void
    {
        $response = $this->post('/register', $this->validData());

        $response->assertRedirect('/profil')
            ->assertSessionHas('success', 'Akun berhasil dibuat. Lengkapi profilmu, ya!');

        $user = User::where('email', 'siti@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('Siti Nurhaliza', $user->name);
        $this->assertSame(UserRole::User, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertTrue(password_verify('rahasia123', $user->password));
        $this->assertDatabaseHas('user_profiles', ['user_id' => $user->id]);

        $this->get('/profil')->assertOk()->assertSee('Lengkapi profilmu');
    }

    public function test_role_sent_in_request_is_ignored(): void
    {
        $this->post('/register', $this->validData(['role' => 'admin', 'status' => 'inactive']));

        $user = User::where('email', 'siti@example.com')->firstOrFail();
        $this->assertSame(UserRole::User, $user->role);
        $this->assertSame(UserStatus::Active, $user->status);
    }

    public function test_email_is_normalized_to_lowercase(): void
    {
        $this->post('/register', $this->validData(['email' => '  Siti@Example.COM ']));

        $this->assertDatabaseHas('users', ['email' => 'siti@example.com']);
    }

    /**
     * @return array<string, array{array<string, string>, string, string}>
     */
    public static function invalidData(): array
    {
        return [
            'nama kosong' => [['name' => ''], 'name', 'Nama lengkap wajib diisi.'],
            'nama terlalu panjang' => [['name' => str_repeat('a', 101)], 'name', 'Nama lengkap maksimal 100 karakter.'],
            'email kosong' => [['email' => ''], 'email', 'Email wajib diisi.'],
            'email tidak valid' => [['email' => 'bukan-email'], 'email', 'Format email tidak valid.'],
            'password kosong' => [['password' => '', 'password_confirmation' => ''], 'password', 'Password wajib diisi.'],
            'password < 8 karakter' => [['password' => 'pendek', 'password_confirmation' => 'pendek'], 'password', 'Password minimal 8 karakter.'],
            'konfirmasi tidak cocok' => [['password_confirmation' => 'beda12345'], 'password', 'Konfirmasi password tidak cocok.'],
        ];
    }

    #[DataProvider('invalidData')]
    public function test_registration_validation(array $overrides, string $field, string $message): void
    {
        $this->from('/register')
            ->post('/register', $this->validData($overrides))
            ->assertRedirect('/register')
            ->assertSessionHasErrors([$field => $message]);

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'siti@example.com']);

        $this->post('/register', $this->validData())
            ->assertSessionHasErrors(['email' => 'Email sudah terdaftar. Silakan masuk atau gunakan email lain.']);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_validation_errors_are_shown_on_the_form(): void
    {
        $this->followingRedirects()
            ->from('/register')
            ->post('/register', $this->validData(['password' => 'pendek', 'password_confirmation' => 'pendek']))
            ->assertSee('Password minimal 8 karakter.')
            ->assertSee('value="siti@example.com"', false);
    }

    public function test_password_fields_have_show_hide_toggle(): void
    {
        $this->get('/register')
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('data-password-toggle="password_confirmation"', false)
            ->assertSee('aria-label="Tampilkan password"', false)
            ->assertDontSee('data-password-toggle="email"', false);
    }

    public function test_invalid_field_is_marked_for_accessibility(): void
    {
        $this->followingRedirects()
            ->from('/register')
            ->post('/register', $this->validData(['email' => 'bukan-email']))
            ->assertSee('aria-invalid="true" aria-describedby="email-error"', false)
            ->assertSee('id="email-error"', false);
    }

    public function test_authenticated_user_cannot_open_register_page(): void
    {
        $this->actingAs(User::factory()->create())->get('/register')->assertRedirect('/');
    }
}
