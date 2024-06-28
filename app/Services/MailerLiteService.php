<?php

namespace App\Services;

use GuzzleHttp\Client;

use Carbon\Carbon;


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
    }

    public function getGroups()
    {
        $response = $this->client->get('groups');
        return json_decode($response->getBody(), true);
    }

    public function createSubscriber($groupId, $email)
    {
        $response = $this->client->post("groups/{$groupId}/subscribers", [
            'json' => [
                'email' => $email,
                'resubscribe' => true,
            ],
        ]);
        return json_decode($response->getBody(), true);
    }

    public function getSubscribers($groupId)
    {
        $response = $this->client->get("groups/{$groupId}/subscribers");
        return json_decode($response->getBody(), true);
    }

    public function getDetailedSubscribers($groupId)
    {
        $response = $this->client->get("groups/{$groupId}/subscribers");
        $subscribers = json_decode($response->getBody(), true);
        return array_map(function ($subscriber) {
            $fields = collect($subscriber['fields'])->keyBy('key');

            return [
                'email' => $subscriber['email'],
                'fName' => $fields->get('fname')['value'] ?? '',
                'lName' => $fields->get('lname')['value'] ?? '',
                'startingDate' => $fields->get('start_date')['value'] ?? '',
            ];
        }, $subscribers);
    }


    
    protected function updateApiKey($country)
    {
        switch (strtoupper($country)) {
            case 'IRELAND':
                $this->apiKey = env('MAILERLITE_API_KEY_IRELAND');
                break;
            case 'BRITAIN':
                $this->apiKey = env('MAILERLITE_API_KEY_BRITAIN');
                break;
            case 'CANADA':
                $this->apiKey = env('MAILERLITE_API_KEY_CANADA');
                break;
            case 'AMERICA':
                $this->apiKey = env('MAILERLITE_API_KEY_AMERICA');
                break;
            case 'AUSTRALIA':
                $this->apiKey = env('MAILERLITE_API_KEY_AUSTRALIA');
                break;
            default:
                throw new \Exception('Invalid country provided');
        }

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
        $dates = [];
        $now = Carbon::now();

        for ($i = 1; $i <= 4; $i++) {
            $dates[] = $now->copy()->addMonths($i)->format('M-Y');
        }

        return $dates;
    }

    

    public function getSubscriberByEmail($email, $country)
    {
        $this->updateApiKey($country);

        $response = $this->client->get("subscribers/search", [
            'query' => [
                'query' => $email,
            ],
        ]);

        $subscribers = json_decode($response->getBody(), true);

        if (empty($subscribers)) {
            return null;
        }

        $subscriber = $subscribers[0];
        $fields = collect($subscriber['fields'])->keyBy('key');
        $startingDate = $fields->get('start_date')['value'] ?? '';

        return [
            'email' => $subscriber['email'],
            'lName' => $fields->get('lname')['value'] ?? '',
            'startingDate' => $startingDate,
            'possible_start_dates' => $this->getPossibleStartDates($startingDate),
            ];
          
    }


     protected function convertToDate($dateString)
    {
        try {
            // Get the current year and month
            $currentYear = Carbon::now()->year;
            $currentMonth = Carbon::now()->month;

            // Parse the month and day from the input string
            $date = Carbon::createFromFormat('M d', $dateString);

            // Use the current year if the input month is less than the current month
            // Otherwise, use the next year
            if ($date->month < $currentMonth) {
                $date->year = $currentYear;
            } else {
                $date->year = $currentYear + 1;
            }

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: " . $dateString);
        }
    }


    public function updateSubscriberStartDate($email, $country, $startDate)
    {        
        $this->updateApiKey($country);

        $subscriber = $this->getSubscriberByEmail($email, $country);
        if (!$subscriber) {
            return null;
        }

        // Convert start date to Y-m-d format
        //$formattedDate = $this->convertToDate($startDate);

        $response = $this->client->put("subscribers/{$subscriber['email']}", [
            'json' => [
                'fields' => [
                    'start_date' => $startDate,
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

}

