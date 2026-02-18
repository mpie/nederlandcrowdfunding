<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admins = [
            ['email' => 'info@mpie.nl', 'name' => 'Mpie'],
            ['email' => 'folkert.eggink@mogelijk.nl', 'name' => 'Folkert Eggink'],
        ];

        foreach ($admins as $admin) {
            $user = User::updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => 'changeme!2026',
                    'email_verified_at' => now(),
                ],
            );

            $user->assignRole('super_admin');
        }
    }
}