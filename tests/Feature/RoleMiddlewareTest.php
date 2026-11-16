<?php

namespace Tests\Feature;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function userWithRole(?string $role): ?User
    {
        return $role ? User::factory()->create(['role' => $role]) : null;
    }

    /**
     * [path, role (null = tamu), status/redirect yang diharapkan]
     *
     * @return array<string, array{string, ?string, int|string}>
     */
    public static function accessMatrix(): array
    {
        return [
            'tamu → halaman publik' => ['/e-learning', null, 200],
            'tamu → profil' => ['/profil', null, '/login'],
            'tamu → panel admin' => ['/admin/dashboard', null, '/login'],
            'tamu → panel super admin' => ['/super-admin/dashboard', null, '/login'],

            'user → profil' => ['/profil', 'user', 200],
            'user → riwayat' => ['/profil/riwayat/kegiatan', 'user', 200],
            'user → panel admin' => ['/admin/dashboard', 'user', 403],
            'user → panel super admin' => ['/super-admin/dashboard', 'user', 403],

            'admin → panel admin' => ['/admin/dashboard', 'admin', 200],
            'admin → manajemen kuis' => ['/admin/quizzes', 'admin', 200],
            'admin → panel super admin' => ['/super-admin/admins', 'admin', 403],
            'admin → profil user' => ['/profil', 'admin', 403],

            'super admin → panel admin' => ['/admin/materials', 'super_admin', 200],
            'super admin → panel super admin' => ['/super-admin/dashboard', 'super_admin', 200],
            'super admin → testimoni' => ['/super-admin/testimonials', 'super_admin', 200],
        ];
    }

    #[DataProvider('accessMatrix')]
    public function test_route_access_by_role(string $path, ?string $role, int|string $expected): void
    {
        if ($user = $this->userWithRole($role)) {
            $this->actingAs($user);
        }

        $response = $this->get($path);

        is_string($expected) ? $response->assertRedirect($expected) : $response->assertStatus($expected);
    }

    public function test_user_deactivated_mid_session_is_logged_out(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profil')->assertOk();

        $user->update(['status' => 'inactive']);

        $this->get('/profil')
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Akun dinonaktifkan. Silakan hubungi admin.']);

        $this->assertGuest();
    }

    public function test_route_model_binding_returns_404_for_missing_record(): void
    {
        $this->get('/e-learning/999')->assertNotFound();

        $material = LearningMaterial::factory()->create(['title' => 'Belajar Excel']);
        $this->get('/e-learning/'.$material->id)->assertOk()->assertSee('Belajar Excel');
    }

    public function test_placeholder_action_redirects_back_with_message(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->from('/admin/materials')
            ->post('/admin/materials')
            ->assertRedirect('/admin/materials')
            ->assertSessionHas('error');
    }

    public function test_admin_sidebar_hides_super_admin_menu(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/dashboard')
            ->assertSee('Manajemen Kegiatan')
            ->assertDontSee('Manajemen Akun Admin');
    }
}
