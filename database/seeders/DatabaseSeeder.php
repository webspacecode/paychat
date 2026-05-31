<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $masterEmail = env('MASTER_ADMIN_EMAIL');
        $masterPassword = env('MASTER_ADMIN_PASSWORD');

        if ($masterEmail && $masterPassword) {
            User::updateOrCreate(
                ['email' => $masterEmail],
                [
                    'name' => env('MASTER_ADMIN_NAME', 'PayChat Master'),
                    'password' => Hash::make($masterPassword),
                    'tenant_id' => null,
                    'role' => 'master',
                ]
            );
        }
    }
}
