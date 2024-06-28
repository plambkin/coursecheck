<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MailerLiteService;
use Illuminate\Support\Facades\Log;

class MailerLiteController extends Controller
{
    protected $mailerLite;

    public function __construct(MailerLiteService $mailerLite)
    {
        $this->mailerLite = $mailerLite;
    }

    public function getGroups()
    {
        $groups = $this->mailerLite->getGroups();
        return view('groups', ['groups' => $groups]);
    }

    public function createSubscriber(Request $request)
    {
        $request->validate([
            'group_id' => 'required',
            'email' => 'required|email',
        ]);

        $subscriber = $this->mailerLite->createSubscriber($request->group_id, $request->email);
        return redirect()->route('groups')->with('status', 'Subscriber added!');
    }

    public function getSubscribers($groupId)
    {
        $subscribers = $this->mailerLite->getSubscribers($groupId);
        return view('subscribers', ['subscribers' => $subscribers]);
    }

    public function getDetailedSubscribers($groupId)
    {
        try {
            $subscribers = $this->mailerLite->getDetailedSubscribers($groupId);
        } catch (\Exception $e) {
            Log::error('Error fetching subscribers: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch subscribers'], 500);
        }

        return view('subscribersDetailed', ['subscribers' => $subscribers]);
    }

    public function getSubscriber(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'country' => 'required|string',
        ]);

        try {
            $subscriber = $this->mailerLite->getSubscriberByEmail($request->email, $request->country);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($subscriber) {
            return response()->json($subscriber);
        } else {
            return response()->json(['error' => 'Subscriber not found'], 404);
        }
    }

}
