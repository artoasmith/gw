<?php
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    public function run(){
        DB::table('users') -> delete();
        DB::table('users')->insert([
            'login' => 'admin',
            'email' => 'hereyouare1987@gmail.com',
            'password' => md5('adminadmin'),
            'user_role'=> '1'
        ]);
    }
}
        
