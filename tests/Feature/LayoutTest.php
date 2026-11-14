<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function makeUser(string $role): User
    {
        return (new User(['name' => 'Budi Santoso', 'email' => 'budi@example.com']))
            ->forceFill(['id' => 1, 'role' => $role]);
    }

    private function renderPanel(): \Illuminate\Testing\TestView
    {
        return $this->blade('@extends("layouts.panel") @section("content") Isi panel @endsection');
    }

    public function test_landing_shows_public_navbar_and_guest_buttons(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder(['Home', 'E-Learning', 'Kegiatan', 'Artikel', 'Tentang Kami']);
        $response->assertSee('Masuk');
        $response->assertSee('Daftar');
        $response->assertDontSee('Lowongan');
        $response->assertDontSee('Sertifikat');
    }

    public function test_landing_shows_avatar_dropdown_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->makeUser('user'))->get('/');

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSeeInOrder(['Profil', 'Riwayat', 'Keluar']);
        $response->assertDontSee('href="'.url('/login').'"', false);
    }

    public function test_panel_shows_admin_menu_without_super_admin_menu_for_admin(): void
    {
        $this->actingAs($this->makeUser('admin'));

        $view = $this->renderPanel();

        $view->assertSeeInOrder([
            'Dashboard', 'Profil', 'Riwayat',
            'Manajemen E-Learning', 'Manajemen Kuis', 'Manajemen Kegiatan',
        ]);
        $view->assertSee('Isi panel');
        $view->assertDontSee('Manajemen Akun Admin');
        $view->assertDontSee('Hero Section');
    }

    public function test_panel_shows_super_admin_menu_for_super_admin(): void
    {
        $this->actingAs($this->makeUser('super_admin'));

        $view = $this->renderPanel();

        $view->assertSee('Manajemen Kegiatan');
        $view->assertSeeInOrder(['Manajemen Akun Admin', 'Hero Section', 'Artikel', 'Testimoni']);
    }
}
