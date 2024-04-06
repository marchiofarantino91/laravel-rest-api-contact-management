<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admins'),
            'name' => 'administrator',
            'token' => 'test',
        ]);
        User::create([
            'username' => 'admin1',
            'password' => Hash::make('admins'),
            'name' => 'administrator1',
            'token' => 'test1',
        ]);
    }
}
