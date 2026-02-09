<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Models\Message;

class MessageController extends Controller
{
    public function sendMessage(Request $request)
    {
        $sender = auth()->user();
        $recipientId = $request->input('to_user_id');
        $messageText = $request->input('message');
        $message = Message::create([
            'from_user_id' => $sender->id,
            'to_user_id' => $recipientId,
            'message' => $messageText,
        ]);
        event(new MessageSent($messageText, $recipientId));

        return response()->json(['status' => 'Message sent!', 'message' => $messageText]);
    }

    public function fetchMessages($userId)
    {
        $currentUser = auth()->id();

        $messages = Message::where(function ($q) use ($currentUser, $userId) {
            $q->where('from_user_id', $currentUser)->where('to_user_id', $userId);
        })->orWhere(function ($q) use ($currentUser, $userId) {
            $q->where('from_user_id', $userId)->where('to_user_id', $currentUser);
        })
        ->orderBy('created_at')
        ->get();

        return response()->json($messages);
    }
        
}
