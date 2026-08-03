<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SatuanSeeder::class);
        $this->call(SupplierSeeder::class);

        User::query()->updateOrCreate(
            ['email' => 'admin@pos.test'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'kasir@pos.test'],
            [
                'name' => 'Kasir',
                'password' => Hash::make('password'),
                'role' => User::ROLE_KASIR,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'owner@pos.test'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OWNER,
            ]
        );
    }
}
