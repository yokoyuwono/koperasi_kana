<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    public function run(): void
    {

        DB::table('users')->insert([
           
            [
                'nama'       => 'Admin Dua',
                'email'      => 'admin2@example.com',
                'password'   => Hash::make('qwer@1234'),
                'role'       => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama'       => 'COA User',
                'email'      => 'coa@example.com',
                'password'   => Hash::make('qwer@1234'),
                'role'       => 'coa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
