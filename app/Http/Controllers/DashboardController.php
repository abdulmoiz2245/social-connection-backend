<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\Request as ConnectionRequest;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //get length of suggestion sent request reciived request and connections
    public function index()
    {
        try {
            // Get the current user
            $user = auth()->user();
            // Get all the other users who are not connected to the current user, and who have not sent or received a request from the current user
            $suggestions = User::where('id', '!=', $user->id)
                ->whereRaw('id NOT IN (SELECT sender_id FROM connections UNION SELECT receiver_id FROM connections)')
                ->whereRaw('id NOT IN (SELECT sender_id FROM requests UNION SELECT receiver_id FROM requests)')
                ->count();

            $userConnections  = $user->connections->count();
            $receivedRequests = ConnectionRequest::where('receiver_id', $user->id)->with('sender')->count(); 
              
            $sentRequests = ConnectionRequest::where('sender_id', $user->id)->with('receiver')->count();
           
            $responseData = [
                'totalSuggestions' => $suggestions,
                'totalConnections' => $userConnections,
                'totalReceivedRequests' => $receivedRequests,
                'totalSentRequests' => $sentRequests
            ];
            $responseSuccess = true;
            $responseMessage = "Data fetched successfully";
            return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
        

        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

}
