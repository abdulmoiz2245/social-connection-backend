<?php

namespace App\Http\Controllers;

use App\Helpers\APIHelper;
use App\Models\User;
use App\Models\Request;

class RequestController extends Controller
{
    // Store action to send a connection request
    public function store()
    {
        try {

            // Validate the request data
            request()->validate([
                'receiver_id' => 'required|exists:users,id'
            ]);
            // Get the current user
            $sender = auth()->user();

            // Get the receiver user by id
            $receiver = User::findOrFail(request('receiver_id'));
            // Check if the sender and receiver are not already connected or have pending requests
            // Create a new request record
            Request::create([
                'sender_id' =>  $sender->id,
                'receiver_id' => $receiver->id,
                'status' => 'pending'
            ]);
            // Return a success message

            $responseData = [];
            $responseSuccess = true;
            $responseMessage = "Request sent successfully";
            return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

    // Index action to show sent requests page
    public function index()
    {
        try {
            // Get the current user
            $user = auth()->user();
            // Get all the requests sent by the current user
            $sentRequests = Request::where('sender_id', $user->id)->with('receiver')->paginate(10); // Paginate the results with 10 per page
            // Return the sent requests view with the sent requests data

            if (empty($sentRequests)) {
                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "No request found";
                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 404);
            } else {
                $responseData = [
                    'sentRequests' => $sentRequests
                ];
                $responseSuccess = true;
                $responseMessage = "Requests fetched successfully";

                return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
            }
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }

    // Destroy action to withdraw a connection request
    public function destroy($id)
    {
        try {
            // Get the current user
            // Get the current user
            $user = auth()->user();
            // Get the request by id
            $request = Request::findOrFail($id);
            // Check if the request belongs to the current user
            if ($request->sender_id == $user->id) {
                // Delete the request record
                $request->delete();
                // Return a success message

                $responseData = [];
                $responseSuccess = true;
                $responseMessage = "Request withdrawn successfully";
            } else {
                // Return an error message

                $responseData = [];
                $responseSuccess = false;
                $responseMessage = "You cannot withdraw this request";
            }

            return response()->json(APIHelper::generateResponseArray($responseSuccess, $responseMessage, $responseData), 200);
        } catch (\Exception $e) {
            return response()->json(APIHelper::generateResponseArray(false, $e->getMessage(), []), 500);
        }
    }
}
