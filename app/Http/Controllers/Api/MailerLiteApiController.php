<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MailerLiteService;
use DateTime;


class MailerLiteApiController extends Controller
{
    protected $mailerLite;

    public function __construct(MailerLiteService $mailerLite)
    {
        $this->mailerLite = $mailerLite;
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


    public function updateCourseStartDate(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'country' => 'required|string',
        'date' => ['required', 'string', 'regex:/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) \d{4}$/']
        ]);



        try {
            $updatedSubscriber = $this->mailerLite->updateSubscriberStartDate(
                $request->email,
                $request->country,
                $request->date
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

        if ($updatedSubscriber) {
            return response()->json(['success' => 'Start date updated', 'subscriber' => $updatedSubscriber]);
        } else {
            return response()->json(['error' => 'Subscriber not found'], 404);
        }
    }

}
