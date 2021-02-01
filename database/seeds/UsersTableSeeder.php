<?php

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        User::create([
            'name' => 'admin',
            'email' => 'hungryntust@gmail.com',
            'phone' => '0123456789',
            'password' => Hash::make('hungryntust'),
            'role' => 1,
            'gender' => 1
        ]);

        User::create([
            'name' => '臭豆腐',
            'email' => 'ptgood0716@gmail.com',
            'phone' => '',
            'password' => Hash::make('password1'),
            'role' => 1,
            'gender' => 1
        ]);

        User::create([
            'name' => '鹹酥雞',
            'email' => 'g0987623673@gmail.com',
            'phone' => '',
            'password' => Hash::make('password1'),
            'role' => 1,
            'gender' => 1
        ]);
    }
}
