<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $user = User::firstOrCreate(
            ['email' => 'info@mpie.nl'],
            [
                'name' => 'Mpie',
                'password' => Hash::make('changeme!2026'),
                'email_verified_at' => now(),
            ],
        );

        $user->assignRole('super_admin');
    }
}