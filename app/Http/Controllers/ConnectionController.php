<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\User;
use App\Models\Request as ConectionRequest;
use App\Models\Connection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConnectionController extends Controller
{
    // Create action to show received requests page
    public function create()
    {
        try {
            $user = auth()->user();
            // Get all the requests received by the current user
            $receivedRequests = ConectionRequest::where('receiver_id', $user->id)->with('sender')->paginate(10); // Paginate the results with 10 per page


            if (empty($receivedRequests)) {
                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "No request found";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 404);
            } else {
                $responseData = [
                    'receivedRequests' => $receivedRequests
                ];
                $responseSuccess = true;
                $responseMessage = "Requests fetched successfully";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
            }
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

    // Store action to accept a connection request
    public function store()
    {
        try {
            // Validate the request data
            request()->validate([
                'request_id' => 'required|exists:requests,id'
            ]);
            $receiver = auth()->user();

            // Get the request by id
            $request = ConectionRequest::findOrFail(request('request_id'));
            // Check if the request belongs to the current user
            if ($request->receiver_id == $receiver->id) {
                // Create a new connection record
                Connection::create([
                    'sender_id' => $request->sender_id,
                    'receiver_id' => $request->receiver_id,
                ]);
                // Delete the request record
                $request->delete();

                $responseData = [];
                $responseSuccess = true;
                $responseMessage = "Request accepted successfully";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
            } else {

                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "You canot accept this request it is not belong to you";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 404);
            }
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

    // Index action to show connections page
    public function index()
    {
        try {
            $user = auth()->user();

            $page = request("page", 1);

            // Get all the connections of the current user
            $result = DB::select("WITH 
          -- Get all connections of authnticated user
          user_connections AS (
            SELECT id, sender_id, receiver_id, created_at, updated_at
            FROM connections
            WHERE sender_id = " . $user->id . " OR receiver_id = " . $user->id . "
          ), 
          -- Get all connections of users tat have 1st connection by using the user_connections
          common_connections AS (
            SELECT c.id, c.sender_id, c.receiver_id, c.created_at, c.updated_at
            FROM connections c
            JOIN user_connections uc ON c.sender_id = uc.sender_id OR c.sender_id = uc.receiver_id 
                OR c.receiver_id = uc.sender_id OR c.receiver_id = uc.receiver_id
          )
          -- Count the number of common connections for each user connection
          SELECT uc.*, COUNT(DISTINCT cc.id) AS common_connections_count
          FROM user_connections uc
          LEFT JOIN common_connections cc ON (cc.sender_id = uc.sender_id  OR cc.sender_id = uc.receiver_id 
                                             OR cc.receiver_id = uc.sender_id OR cc.receiver_id = uc.receiver_id)
                                           AND (cc.sender_id <> " . $user->id . " AND cc.receiver_id <> " . $user->id . ") 
          GROUP BY uc.id order by common_connections_count  desc;
          ");

            $collection = collect($result);
            $collection = $collection->map(function ($item) {

                $user_id = $item->sender_id == auth()->user()->id ? $item->receiver_id : $item->sender_id;

                $user = User::find($user_id);

                $item->user = $user;

                return $item;
            });

            $totalCount = $collection->count();


            $collection = collect($result);


            $sorted = $collection;

            $num_per_page = 10;
            if (!$page) {
                $page = 1;
            }

            $offset = ($page - 1) * $num_per_page;
            $sorted = $sorted->splice($offset, $num_per_page);
            $userConnections = new \Illuminate\Pagination\LengthAwarePaginator($sorted, $totalCount, $num_per_page, $page);


            if (empty($userConnections)) {
                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "No connection found";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 404);
            } else {
                $responseData = [
                    'connections' => $userConnections,
                ];
                $responseSuccess = true;
                $responseMessage = "Connections fetched successfully";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
            }
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

    // Show action to show connections in common with a user
    public function show($id)
    {
        try {
            $user = auth()->user();
            $otherUser = User::findOrFail($id);
            $userConnections  = $otherUser->connections;

            $userConnections = $userConnections->map(function ($connection) use ($otherUser) {
                if ($otherUser->id == $connection->sender_id) {
                    $connection->user = User::find($connection->receiver_id);
                } else {
                    $connection->user = User::find($connection->sender_id);
                }
                return $connection;
            });
            $userConnections = $userConnections->filter(function ($connection) use ($user) {
                return $connection->sender_id != $user->id &&  $connection->receiver_id != $user->id;
            })->values();


            if (empty($userConnections)) {
                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "No connection found";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 404);
            } else {
                $responseData = [
                    'connectionsInCommon' => $userConnections,
                ];
                $responseSuccess = true;
                $responseMessage = "Connections fetched successfully";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
            }
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

    // Destroy action to remove a connection
    public function destroy($id)
    {



        try {

            $user = auth()->user();
            $connection = Connection::findOrFail($id);
            // Check if the connection belongs to the current user
            if ($connection->sender_id == $user->id || $connection->receiver_id == $user->id) {
                // Delete the connection record
                $connection->delete();

                $responseData = [];
                $responseSuccess = true;
                $responseMessage = "Connection removed successfully";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
            } else {

                return response()->json(['message' => 'You cannot remove this connection.'], 403);

                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "You cannot remove this connection.";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 403);
            }
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }
}
