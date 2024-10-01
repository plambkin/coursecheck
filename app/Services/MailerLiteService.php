<?php

namespace App\Services;

use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MailerLiteService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('MAILERLITE_API_KEY');
        $this->client = new Client([
            'base_uri' => 'https://api.mailerlite.com/api/v2/',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-MailerLite-ApiKey' => $this->apiKey,
            ],
        ]);
        Log::info("MailerLiteService initialized with API Key ending in " . substr($this->apiKey, -4));
    }

    public function getGroups()
    {
        Log::info("Fetching groups from MailerLite API.");
        try {
            $response = $this->client->get('groups');
            Log::info("Groups fetched successfully.");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error fetching groups: " . $e->getMessage());
            return null;
        }
    }

    public function createSubscriber($groupId, $email)
    {
        Log::info("Creating a new subscriber in group ID: {$groupId}, for email: {$email}");
        try {
            $response = $this->client->post("groups/{$groupId}/subscribers", [
                'json' => [
                    'email' => $email,
                    'resubscribe' => true,
                ],
            ]);
            Log::info("Subscriber created successfully for {$email}.");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error creating subscriber: " . $e->getMessage());
            return null;
        }
    }

    public function getSubscribers($groupId)
    {
        Log::info("Fetching subscribers from group ID: {$groupId}");
        try {
            $response = $this->client->get("groups/{$groupId}/subscribers");
            Log::info("Subscribers fetched successfully for group ID: {$groupId}");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error fetching subscribers: " . $e->getMessage());
            return null;
        }
    }

    public function getDetailedSubscribers($groupId)
    {
        Log::info("Fetching detailed subscribers for group ID: {$groupId}");
        try {
            $response = $this->client->get("groups/{$groupId}/subscribers");
            $subscribers = json_decode($response->getBody(), true);

            Log::info("Mapping detailed subscriber fields for group ID: {$groupId}");
            return array_map(function ($subscriber) {
                $fields = collect($subscriber['fields'])->keyBy('key');

                return [
                    'email' => $subscriber['email'],
                    'fName' => $fields->get('fname')['value'] ?? '',
                    'lName' => $fields->get('lname')['value'] ?? '',
                    'startingDate' => $fields->get('start_date')['value'] ?? '',
                ];
            }, $subscribers);
        } catch (\Exception $e) {
            Log::error("Error fetching detailed subscribers: " . $e->getMessage());
            return null;
        }
    }

    public function updateApiKey($country)
    {
        Log::info("Updating API key for country: {$country}");

        switch (strtoupper($country)) {
            case 'IRELAND':
                $this->apiKey = config('app.mailer_lite.ireland');
                break;
            case 'BRITAIN':
                $this->apiKey = config('app.mailer_lite.britain');
                break;
            case 'AUSTRALIA':
                $this->apiKey = config('app.mailer_lite.australia');
                break;
            case 'AMERICA':
                $this->apiKey = config('app.mailer_lite.america');
                break;
            case 'CANADA':
                $this->apiKey = config('app.mailer_lite.canada');
                break;
            default:
                throw new \Exception('Invalid country provided');
        }

        Log::info("API key updated for {$country}, key ending in " . substr($this->apiKey, -4));

        // Recreate the client with the new API key
        $this->client = new Client([
            'base_uri' => 'https://api.mailerlite.com/api/v2/',
            'headers' => [
                'Content-Type' => 'application/json',
                'X-MailerLite-ApiKey' => $this->apiKey,
            ],
        ]);
    }

    public function getPossibleStartDates()
    {
        Log::info("Generating possible start dates.");
        $dates = [];
        $now = Carbon::now();

        for ($i = 1; $i <= 4; $i++) {
            $dates[] = $now->copy()->addMonths($i)->format('M-Y');
        }

        Log::info("Possible start dates generated: " . implode(', ', $dates));
        return $dates;
    }

    public function getSubscriberByEmail($email, $country)
    {
        Log::info("Fetching subscriber by email: {$email} for country: {$country}");
        $this->updateApiKey($country);

        try {
            $response = $this->client->get("subscribers/search", [
                'query' => [
                    'query' => $email,
                ],
            ]);

            $subscribers = json_decode($response->getBody(), true);

            if (empty($subscribers)) {
                Log::info("No subscriber found for email: {$email} in country: {$country}");
                return null;
            }

            $subscriber = $subscribers[0];
            $fields = collect($subscriber['fields'])->keyBy('key');
            $startingDate = $fields->get('start_date')['value'] ?? '';

            Log::info("Subscriber found: {$email}, starting date: {$startingDate}");

            return [
                'email' => $subscriber['email'],
               'fName' => $fields->get('fname')['value'] ?? '',
                'lName' => $fields->get('lname')['value'] ?? '',
                'startingDate' => $startingDate,
                'possible_start_dates' => $this->getPossibleStartDates($startingDate),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching subscriber by email: " . $e->getMessage());
            return null;
        }
    }

    protected function convertToDate($dateString)
    {
        try {
            Log::info("Converting date string: {$dateString}");

            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            $date = Carbon::createFromFormat('M d', $dateString);

            if ($date->month < $currentMonth) {
                $date->year = $currentYear;
            } else {
                $date->year = $currentYear + 1;
            }

            Log::info("Date converted: " . $date->format('Y-m-d'));
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            Log::error("Invalid date format: {$dateString}, error: " . $e->getMessage());
            throw new \Exception("Invalid date format: " . $dateString);
        }
    }

    public function updateSubscriberStartDate($email, $country, $startDate)
    {
        Log::info("Updating subscriber start date for {$email} in {$country} with start date: {$startDate}");
        $this->updateApiKey($country);

        $subscriber = $this->getSubscriberByEmail($email, $country);
        if (!$subscriber) {
            Log::error("Subscriber not found for email: {$email} in country: {$country}");
            return null;
        }

        try {
            $response = $this->client->put("subscribers/{$subscriber['email']}", [
                'json' => [
                    'fields' => [
                        'start_date' => $startDate,
                    ],
                ],
            ]);

            Log::info("Subscriber start date updated successfully for {$email}.");
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error("Error updating start date for subscriber: {$email}, error: " . $e->getMessage());
            return null;
        }
    }


        // MailerLiteController.php

    public function getSubscriber(Request $request)
    {
        Log::info("Fetching subscriber.", ['email' => $request->email]);

        $validatedData = $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $subscriber = $this->mailerLite->getSubscriberByEmail($validatedData['email'], 'IRELAND'); // Assuming the country is Ireland for this example.
            if ($subscriber) {
                Log::info("Subscriber fetched successfully.", ['email' => $validatedData['email']]);
                return response()->json($subscriber);
            } else {
                Log::warning("Subscriber not found.", ['email' => $validatedData['email']]);
                return response()->json(['error' => 'Subscriber not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error("Error fetching subscriber: " . $e->getMessage(), ['email' => $validatedData['email']]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function downloadSubscribersCsv(Request $request)
    {
        Log::info('Starting CSV download process.');

        if ($request->email !== 'plambkin100@gmail.com') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $this->downloadSubscribersCsv(); // Ensure this method generates CSV correctly
    }

}
