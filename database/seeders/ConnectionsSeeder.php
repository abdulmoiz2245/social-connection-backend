<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Connection;
use Illuminate\Database\Seeder;

class ConnectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::find(1);
        $users = User::all();


        // Get a random number of connections to make (between 1 and 10)
        $connectionsCount = 30;

        $connectionIds = Connection::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->pluck('id');

        // Get the users who have not connected with the current user
        $connectors = $users->where('id', '!=', $user->id)
            ->whereNotIn('id', $connectionIds)
            ->random($connectionsCount);

        foreach ($connectors as $connector) {
            Connection::create([
                'sender_id' => $user->id,
                'receiver_id' => $connector->id
            ]);
        }
    }
}
