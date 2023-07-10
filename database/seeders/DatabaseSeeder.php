<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\UsersSeeder;
use Database\Seeders\RequestsSeeder;
use Database\Seeders\ConnectionsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void 
     */
    public function run()
    {
        // Call the seeders in this order
        $this->call(UsersSeeder::class);
        $this->call(RequestsSeeder::class);
        $this->call(ConnectionsSeeder::class);
    }
}    
