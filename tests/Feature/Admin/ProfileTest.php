<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * PBI-07 Melengkapi profil admin (PRD §7 F3).
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rina Pratiwi',
            'phone' => '081234567890',
            'institution' => 'Komunitas Digital Talent Bandung',
            'bio' => 'Mentor karier untuk lulusan baru.',
        ], $overrides);
    }

    // --- Tampilan ---

    public function test_admin_can_view_profile_page_with_readonly_email(): void
    {
        $admin = User::factory()->admin()->withProfile(['phone' => '081234567890'])->create();

        $this->actingAs($admin)
            ->get('/admin/profil')
            ->assertOk()
            ->assertSee('Profil Admin')
            ->assertSee('Riwayat Aktivitas')
            ->assertSee($admin->email)
            ->assertSee('readonly', false)
            ->assertDontSee('Lengkapi profilmu agar identitas pengelola jelas.');
    }

    public function test_incomplete_profile_shows_completion_alert(): void
    {
        $admin = User::factory()->admin()->withProfile(['phone' => null])->create();

        $this->actingAs($admin)
            ->get('/admin/profil')
            ->assertOk()
            ->assertSee('Lengkapi profilmu agar identitas pengelola jelas.');
    }

    public function test_admin_without_profile_row_can_open_page(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/admin/profil')
            ->assertOk()
            ->assertSee('Lengkapi profilmu agar identitas pengelola jelas.');
    }

    // --- Simpan ---

    public function test_admin_can_save_profile_with_photo(): void
    {
        $admin = User::factory()->admin()->withProfile(['phone' => null])->create();

        $response = $this->actingAs($admin)
            ->from('/admin/profil')
            ->put('/admin/profil', $this->validPayload([
                'avatar' => UploadedFile::fake()->create('foto.jpg', 500, 'image/jpeg'),
            ]));

        $response->assertRedirect('/admin/profil')
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success', 'Profil berhasil disimpan');

        $this->assertSame('Rina Pratiwi', $admin->fresh()->name);

        $profile = $admin->fresh()->profile;
        $this->assertSame('081234567890', $profile->phone);
        $this->assertSame('Komunitas Digital Talent Bandung', $profile->institution);
        $this->assertSame('Mentor karier untuk lulusan baru.', $profile->bio);
        $this->assertStringStartsWith('avatars/', $profile->avatar_path);
        Storage::disk('public')->assertExists($profile->avatar_path);

        $this->actingAs($admin->fresh())
            ->get('/admin/profil')
            ->assertSee('Profil berhasil disimpan')
            ->assertSee($profile->avatar_url, false)
            ->assertDontSee('Lengkapi profilmu agar identitas pengelola jelas.');
    }

    public function test_profile_row_is_created_when_missing(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('user_profiles', [
            'user_id' => $admin->id,
            'phone' => '081234567890',
        ]);
    }

    public function test_phone_is_normalized_before_saving(): void
    {
        $admin = User::factory()->admin()->withProfile()->create();

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload(['phone' => '+62 812-3456-7890']))
            ->assertSessionHasNoErrors();

        $this->assertSame('+6281234567890', $admin->profile()->first()->phone);
    }

    public function test_replacing_photo_deletes_old_file(): void
    {
        Storage::disk('public')->put('avatars/lama.jpg', 'lama');
        $admin = User::factory()->admin()->withProfile(['avatar_path' => 'avatars/lama.jpg'])->create();

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload([
                'avatar' => UploadedFile::fake()->create('baru.png', 200, 'image/png'),
            ]))
            ->assertSessionHasNoErrors();

        $newPath = $admin->profile()->first()->avatar_path;
        $this->assertNotSame('avatars/lama.jpg', $newPath);
        Storage::disk('public')->assertMissing('avatars/lama.jpg');
        Storage::disk('public')->assertExists($newPath);
    }

    public function test_saving_without_new_photo_keeps_old_photo(): void
    {
        Storage::disk('public')->put('avatars/lama.jpg', 'lama');
        $admin = User::factory()->admin()->withProfile(['avatar_path' => 'avatars/lama.jpg'])->create();

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload())
            ->assertSessionHasNoErrors();

        $this->assertSame('avatars/lama.jpg', $admin->profile()->first()->avatar_path);
        Storage::disk('public')->assertExists('avatars/lama.jpg');
    }

    public function test_photo_url_follows_current_host_not_app_url(): void
    {
        config(['app.url' => 'http://localhost']);
        $admin = User::factory()->admin()->withProfile(['avatar_path' => 'avatars/foto.jpg'])->create();

        $this->actingAs($admin)
            ->get('http://127.0.0.1:8001/admin/profil')
            ->assertOk()
            ->assertSee('src="http://127.0.0.1:8001/storage/avatars/foto.jpg"', false)
            ->assertDontSee('http://localhost/storage/avatars/foto.jpg', false);
    }

    public function test_super_admin_can_save_profile(): void
    {
        $superAdmin = User::factory()->superAdmin()->withProfile()->create();

        $this->actingAs($superAdmin)
            ->put('/admin/profil', $this->validPayload(['name' => 'Super Admin Baru']))
            ->assertSessionHas('success', 'Profil berhasil disimpan');

        $this->assertSame('Super Admin Baru', $superAdmin->fresh()->name);
    }

    public function test_email_cannot_be_changed_from_profile_form(): void
    {
        $admin = User::factory()->admin()->withProfile()->create(['email' => 'admin@abislulus.test']);

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload(['email' => 'lain@abislulus.test']))
            ->assertSessionHasNoErrors();

        $this->assertSame('admin@abislulus.test', $admin->fresh()->email);
    }

    // --- Validasi ---

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidPhones(): array
    {
        return [
            'kosong' => [''],
            'mengandung huruf' => ['0812abc45678'],
            'kurang dari 10 digit' => ['081234567'],
            'lebih dari 15 digit' => ['0812345678901234'],
            'tanda + di tengah' => ['0812+34567890'],
        ];
    }

    #[DataProvider('invalidPhones')]
    public function test_invalid_phone_is_rejected(mixed $phone): void
    {
        $admin = User::factory()->admin()->withProfile(['phone' => '081111111111'])->create(['name' => 'Nama Lama']);

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->put('/admin/profil', $this->validPayload(['phone' => $phone]))
            ->assertRedirect('/admin/profil')
            ->assertSessionHasErrors('phone');

        $this->assertSame('Nama Lama', $admin->fresh()->name);
        $this->assertSame('081111111111', $admin->profile()->first()->phone);
    }

    public function test_validation_error_is_shown_below_field(): void
    {
        $admin = User::factory()->admin()->withProfile()->create();

        $this->actingAs($admin)
            ->from('/admin/profil')
            ->followingRedirects()
            ->put('/admin/profil', $this->validPayload(['phone' => 'abc']))
            ->assertSee('is-invalid', false)
            ->assertSee('No. HP hanya boleh berisi angka (boleh diawali +) dan terdiri dari 10–15 digit.');
    }

    public function test_name_is_required_and_limited(): void
    {
        $admin = User::factory()->admin()->withProfile()->create();

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload(['name' => '']))
            ->assertSessionHasErrors(['name' => 'Nama lengkap wajib diisi.']);

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload([
                'name' => str_repeat('a', 101),
                'institution' => str_repeat('b', 151),
                'bio' => str_repeat('c', 1001),
            ]))
            ->assertSessionHasErrors(['name', 'institution', 'bio']);
    }

    public function test_photo_must_be_jpg_or_png_and_max_2mb(): void
    {
        $admin = User::factory()->admin()->withProfile()->create();

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload([
                'avatar' => UploadedFile::fake()->create('besar.jpg', 2049, 'image/jpeg'),
            ]))
            ->assertSessionHasErrors(['avatar' => 'Ukuran foto profil maksimal 2 MB.']);

        $this->actingAs($admin)
            ->put('/admin/profil', $this->validPayload([
                'avatar' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
            ]))
            ->assertSessionHasErrors('avatar');

        $this->assertEmpty(Storage::disk('public')->allFiles('avatars'));
    }

    // --- Akses ---

    public function test_regular_user_is_forbidden(): void
    {
        $user = User::factory()->withProfile()->create();

        $this->actingAs($user)->get('/admin/profil')->assertForbidden();
        $this->actingAs($user)->put('/admin/profil', $this->validPayload())->assertForbidden();

        $this->assertNotSame('Rina Pratiwi', $user->fresh()->name);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/profil')->assertRedirect('/login');
        $this->put('/admin/profil', $this->validPayload())->assertRedirect('/login');
    }

    public function test_policy_only_allows_profile_owner(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->admin()->withProfile()->create();

        $this->assertTrue($admin->can('update', new UserProfile(['user_id' => $admin->id])));
        $this->assertFalse($admin->can('update', $other->profile));
    }

    // --- Navigasi ---

    public function test_navbar_dropdown_shows_profile_link_for_admin(): void
    {
        $admin = User::factory()->admin()->withProfile()->create();

        $this->actingAs($admin)
            ->get('/')
            ->assertOk()
            ->assertSee(route('admin.profile.edit'), false)
            ->assertSeeInOrder(['Profil', 'Panel Admin', 'Keluar']);
    }
}
