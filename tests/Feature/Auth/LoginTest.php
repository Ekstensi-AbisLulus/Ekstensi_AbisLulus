<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke AbisLulus')
            ->assertSee('Selamat datang kembali!')
            ->assertSee('Ingat saya')
            ->assertSee('data-password-toggle="password"', false)
            ->assertSee('images/auth-illustration.png', false)
            ->assertSee('href="'.route('password.request').'"', false)
            ->assertSee('href="'.route('register').'"', false);
    }

    public function test_login_page_shows_status_message(): void
    {
        $this->withSession(['status' => 'Password berhasil diubah. Silakan masuk dengan password baru.'])
            ->get('/login')
            ->assertSee('Password berhasil diubah. Silakan masuk dengan password baru.');
    }

    public function test_remember_me_sets_recaller_cookie(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => '1']);

        $response->assertCookie(auth()->guard()->getRecallerName());
        $this->assertNotNull($user->fresh()->remember_token);
    }

    public function test_email_is_case_insensitive(): void
    {
        $user = User::factory()->create(['email' => 'siti@example.com']);

        $this->post('/login', ['email' => '  Siti@Example.com ', 'password' => 'password'])->assertRedirect('/');

        $this->assertAuthenticatedAs($user);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function roleRedirects(): array
    {
        return [
            'user ke beranda' => ['user', '/'],
            'admin ke dashboard admin' => ['admin', '/admin/dashboard'],
            'super admin ke dashboard super admin' => ['super_admin', '/super-admin/dashboard'],
        ];
    }

    #[DataProvider('roleRedirects')]
    public function test_login_redirects_by_role(string $role, string $expectedPath): void
    {
        $user = User::factory()->create(['role' => $role]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect($expectedPath);

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_shows_generic_error(): void
    {
        $user = User::factory()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'salah-total'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_unknown_email_shows_same_generic_error(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'tidakada@example.com', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);

        $this->assertGuest();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->inactive()->create();

        $this->from('/login')
            ->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Akun dinonaktifkan. Silakan hubungi admin.']);

        $this->assertGuest();
    }

    public function test_inactive_message_is_not_shown_for_wrong_password(): void
    {
        $user = User::factory()->inactive()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'salah-total'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah.']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $this->post('/login', [])
            ->assertSessionHasErrors(['email' => 'Email wajib diisi.', 'password' => 'Password wajib diisi.']);
    }

    public function test_login_is_rate_limited(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'salah-total']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_role_home(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/login')->assertRedirect('/admin/dashboard');
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect('/');

        $this->assertGuest();
    }
}
