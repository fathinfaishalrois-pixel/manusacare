<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_login_page_resolves_auth_controller_and_renders(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke akun')
            ->assertSee('Daftar sekarang');
    }

    public function test_registration_page_renders(): void
    {
        $this->get('/daftar')
            ->assertOk()
            ->assertSee('Buat akun baru')
            ->assertSee('Daftar &amp; Masuk', false);
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Administrator login');
    }
}
