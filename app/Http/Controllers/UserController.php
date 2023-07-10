<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Index action to show suggestions page
    public function index()
    {
        try {
            // Get the current user
            $user = auth()->user();
            // Get all the other users who are not connected to the current user, and who have not sent or received a request from the current user
            $suggestions = User::where('id', '!=', $user->id)
                ->whereRaw('id NOT IN (SELECT sender_id FROM connections UNION SELECT receiver_id FROM connections)')
                ->whereRaw('id NOT IN (SELECT sender_id FROM requests UNION SELECT receiver_id FROM requests)')
                ->paginate(10);

            if (empty($suggestions)) {
                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "No suggestion found";
            } else {
                $responseData = [
                    'suggestions' => $suggestions
                ];
                $responseSuccess = true;
                $responseMessage = "Suggestions fetched successfully";
            }

            return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }
}
