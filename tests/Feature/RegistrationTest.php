<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_creates_reporter_and_logs_user_in(): void
    {
        $response = $this->post('/daftar', [
            'name' => 'Pengguna Uji',
            'identity_type' => 'nis',
            'identity_number' => '12345678',
            'email' => 'siswa@example.test',
            'phone' => '+62 812-3456-7890',
            'class_or_position' => 'Kelas X',
            'password' => 'PasswordAman123',
            'password_confirmation' => 'PasswordAman123',
            'terms' => '1',
        ]);

        $user = User::where('identity_number', '12345678')->firstOrFail();
        $response->assertRedirect(route('reporter.dashboard'));
        $this->assertAuthenticatedAs($user, 'web');
        $this->assertTrue($user->hasRole('reporter'));
        $this->assertFalse($user->hasRole('admin'));
        $this->assertSame('nis', $user->identity_type);
        $this->assertSame('081234567890', $user->phone);
    }

    public function test_registration_rejects_invalid_nik_length(): void
    {
        $this->from('/daftar')->post('/daftar', [
            'name' => 'Pengguna Uji',
            'identity_type' => 'nik',
            'identity_number' => '1234567890123',
            'password' => 'PasswordAman123',
            'password_confirmation' => 'PasswordAman123',
            'terms' => '1',
        ])->assertSessionHasErrors('identity_number');
    }

    public function test_registration_rejects_invalid_phone_number(): void
    {
        $this->from('/daftar')->post('/daftar', [
            'name' => 'Pengguna Uji',
            'identity_type' => 'nis',
            'identity_number' => '12345678',
            'phone' => '0215550100',
            'password' => 'PasswordAman123',
            'password_confirmation' => 'PasswordAman123',
            'terms' => '1',
        ])->assertSessionHasErrors('phone');
    }

    public function test_registration_page_is_available_to_guests(): void
    {
        $this->get('/daftar')->assertOk();
    }
}
