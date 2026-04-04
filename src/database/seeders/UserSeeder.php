<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
            'name' => 'テストユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('testpassword'),
            'is_admin' => false,
        ]);

        // 管理者の作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('adminpassword'),
            'is_admin' => true,
        ]);
    }
}
