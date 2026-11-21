<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

Artisan::command('manusacare:about', function () {
    $this->info('MANUSACARE — Building a Better School Together');
});

Artisan::command('manusacare:reset-admin', function () {
    $role = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Admin']);
    $admin = User::firstOrNew(['identity_number' => 'admin']);
    $admin->name = $admin->name ?: 'Administrator MANUSACARE';
    $admin->email = 'admin@manusacare.local';
    $admin->password = Hash::make('ChangeMe123!');
    $admin->is_active = true;
    $admin->save();
    $admin->roles()->syncWithoutDetaching([$role->id]);

    $this->info('Akun admin lokal berhasil dipulihkan.');
    $this->line('ID: admin');
    $this->line('Email: admin@manusacare.local');
    $this->line('Password: ChangeMe123!');
    $this->warn('Ganti password setelah berhasil masuk.');
})->purpose('Memulihkan akun administrator lokal MANUSACARE.');
