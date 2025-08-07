<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    User::create([
        'name' => 'Admin User',
        'email' => 'admin@orcas.test',
        'password' => Hash::make('password'),
        'role' => 'admin',
    ]);

    User::create([
        'name' => 'Cashier User',
        'email' => 'cashier@orcas.test',
        'password' => Hash::make('password'),
        'role' => 'cashier',
    ]);

    User::create([
        'name' => 'Agent User',
        'email' => 'agent@orcas.test',
        'password' => Hash::make('password'),
        'role' => 'agent',
    ]);
}
}
