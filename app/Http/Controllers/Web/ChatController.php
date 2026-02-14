<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Chat\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request): View
    {
        $conversations = Auth::user()
            ->conversations()
            ->with(['participants:id,name', 'latestMessage.sender:id,name'])
            ->latest('updated_at')
            ->get();

        $activeConversation = null;
        if ($request->has('conversation')) {
            $activeConversation = Conversation::with(['participants:id,name'])
                ->find($request->get('conversation'));
            
            if ($activeConversation && $activeConversation->hasParticipant(Auth::id())) {
                $activeConversation->markAsRead(Auth::id());
            } else {
                $activeConversation = null;
            }
        }

        return view('chat.index', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
        ]);
    }
}
