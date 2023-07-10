<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Request;
use Illuminate\Database\Seeder;

class RequestsSeeder extends Seeder
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

        // Get a random number of requests to send (between 1 and 5)
        $requestsCount =30;

        // Get a random subset of users who are not the current user, and who have not sent or received a request from the current user
        $sentRequestIds = Request::where('sender_id', $user->id)->pluck('receiver_id');

        // Get the ids of the users who have received requests from the current user
        $receivedRequestIds = Request::where('receiver_id', $user->id)->pluck('sender_id');

        // Get the users who have not sent or received any requests from the current user
        $receivers = $users->where('id', '!=', $user->id)
            ->whereNotIn('id', $sentRequestIds)
            ->whereNotIn('id', $receivedRequestIds)
            ->random($requestsCount);

        foreach ($receivers as $receiver) {

            Request::factory()->create([
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'status' => collect(['pending', 'accepted', 'rejected'])->random(),
            ]);
        }
    }
}
