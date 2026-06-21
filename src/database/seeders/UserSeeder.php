<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => '一般ユーザー1',
            'email' => 'user1@example.com',
            'password' => bcrypt('password'),
            'admin_status' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => '一般ユーザー2',
            'email' => 'user2@example.com',
            'password' => bcrypt('password'),
            'admin_status' => false,
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => '管理者ユーザー',
            'email' => 'user3@example.com',
            'password' => bcrypt('password'),
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);
    }
}