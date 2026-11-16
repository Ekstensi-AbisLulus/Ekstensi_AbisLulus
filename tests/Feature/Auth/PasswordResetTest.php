<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const SENT_MESSAGE = 'Jika email terdaftar, link reset password sudah dikirim. Cek kotak masuk email kamu.';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Notification::fake();
    }

    public function test_forgot_password_page_renders(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Lupa Password')
            ->assertSee('Kirim Link Reset');
    }

    public function test_reset_link_is_sent_to_registered_email(): void
    {
        $user = User::factory()->create();

        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => $user->email])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', self::SENT_MESSAGE);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    public function test_unregistered_email_gets_the_same_response(): void
    {
        $this->from('/forgot-password')
            ->post('/forgot-password', ['email' => 'tidakada@example.com'])
            ->assertRedirect('/forgot-password')
            ->assertSessionHas('status', self::SENT_MESSAGE)
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_forgot_password_requires_valid_email(): void
    {
        $this->post('/forgot-password', ['email' => 'bukan-email'])
            ->assertSessionHasErrors(['email' => 'Format email tidak valid.']);
    }

    public function test_reset_email_is_in_indonesian_and_contains_link(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $mail = $notification->toMail($user);

            return $mail->subject === 'Reset Password AbisLulus'
                && str_contains($mail->actionUrl, '/reset-password/'.$notification->token)
                && in_array('Link ini berlaku selama 60 menit.', array_merge($mail->introLines, $mail->outroLines), true);
        });
    }

    public function test_reset_page_renders_with_token_and_email(): void
    {
        $this->get('/reset-password/token-contoh?email=siti@example.com')
            ->assertOk()
            ->assertSee('Atur Ulang Password')
            ->assertSee('value="token-contoh"', false)
            ->assertSee('value="siti@example.com"', false);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ])
            ->assertRedirect('/login')
            ->assertSessionHas('status', 'Password berhasil diubah. Silakan masuk dengan password baru.');

        $this->assertTrue(password_verify('passwordbaru1', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);

        $this->post('/login', ['email' => $user->email, 'password' => 'passwordbaru1'])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    public function test_full_flow_from_forgot_password_email(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class, function (ResetPasswordNotification $notification) use ($user) {
            $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'passwordbaru1',
                'password_confirmation' => 'passwordbaru1',
            ])->assertRedirect('/login');

            return true;
        });

        $this->assertTrue(password_verify('passwordbaru1', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->from('/reset-password/salah')
            ->post('/reset-password', [
                'token' => 'token-salah',
                'email' => $user->email,
                'password' => 'passwordbaru1',
                'password_confirmation' => 'passwordbaru1',
            ])
            ->assertRedirect('/reset-password/salah')
            ->assertSessionHasErrors(['email' => 'Link reset password tidak valid atau sudah kedaluwarsa. Silakan minta link baru.']);

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }

    public function test_token_expires_after_60_minutes(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->travel(61)->minutes();

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'passwordbaru1',
            'password_confirmation' => 'passwordbaru1',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }

    public function test_new_password_is_validated(): void
    {
        $user = User::factory()->create();
        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertSessionHasErrors(['password']);

        $this->assertTrue(password_verify('password', $user->fresh()->password));
    }
}
