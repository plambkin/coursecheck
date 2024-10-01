<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Import the correct Controller class

use App\Services\MailerLiteService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailerLiteAPIController extends Controller
{
    protected $mailerLite;

    public function __construct(MailerLiteService $mailerLite)
    {
        $this->mailerLite = $mailerLite;
        Log::info("MailerLiteAPIController instantiated.");
    }

    public function getSubscriber(Request $request)
    {
        Log::info("Fetching subscriber via API.", ['email' => $request->email, 'country' => $request->country]);

        $validatedData = $request->validate([
            'email' => 'required|email',
            'country' => 'required|string',
        ]);

        try {
            $subscriber = $this->mailerLite->getSubscriberByEmail($validatedData['email'], $validatedData['country']);

            if ($subscriber) {
                Log::info("Subscriber fetched successfully.", ['email' => $validatedData['email'], 'country' => $validatedData['country']]);
                return response()->json([
                    'success' => true,
                    'subscriber' => $subscriber,
                    'country' => $validatedData['country'],
                ]);
            } else {
                Log::warning("Subscriber not found.", ['email' => $validatedData['email'], 'country' => $validatedData['country']]);
                return response()->json(['success' => false, 'message' => 'Subscriber not found'], 404);
            }
        } catch (\Exception $e) {
            Log::error("Error fetching subscriber: " . $e->getMessage(), ['email' => $validatedData['email'], 'country' => $validatedData['country']]);
            return response()->json(['success' => false, 'message' => 'Error fetching subscriber: ' . $e->getMessage()], 400);
        }
    }

    public function getGroups()
    {
        Log::info("Fetching groups via API.");

        try {
            $groups = $this->mailerLite->getGroups();
            Log::info("Groups fetched successfully.");
            return response()->json(['success' => true, 'groups' => $groups]);
        } catch (\Exception $e) {
            Log::error("Error fetching groups: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Unable to fetch groups'], 500);
        }
    }

    public function createSubscriber(Request $request)
    {
        Log::info("Creating subscriber via API.", ['group_id' => $request->group_id, 'email' => $request->email]);

        $validatedData = $request->validate([
            'group_id' => 'required',
            'email' => 'required|email',
        ]);

        try {
            $subscriber = $this->mailerLite->createSubscriber($validatedData['group_id'], $validatedData['email']);
            Log::info("Subscriber created successfully.");
            return response()->json(['success' => true, 'subscriber' => $subscriber]);
        } catch (\Exception $e) {
            Log::error("Error creating subscriber: " . $e->getMessage(), ['group_id' => $request->group_id, 'email' => $request->email]);
            return response()->json(['success' => false, 'message' => 'Unable to add subscriber.'], 500);
        }
    }

    public function getSubscribers($groupId)
    {
        Log::info("Fetching subscribers for group via API.", ['group_id' => $groupId]);

        try {
            $subscribers = $this->mailerLite->getSubscribers($groupId);
            Log::info("Subscribers fetched successfully.", ['group_id' => $groupId, 'subscriber_count' => count($subscribers)]);
            return response()->json(['success' => true, 'subscribers' => $subscribers]);
        } catch (\Exception $e) {
            Log::error("Error fetching subscribers: " . $e->getMessage(), ['group_id' => $groupId]);
            return response()->json(['success' => false, 'message' => 'Unable to fetch subscribers.'], 500);
        }
    }

    public function getDetailedSubscribers($groupId)
    {
        Log::info("Fetching detailed subscribers for group via API.", ['group_id' => $groupId]);

        try {
            $subscribers = $this->mailerLite->getDetailedSubscribers($groupId);
            Log::info("Detailed subscribers fetched successfully.", ['group_id' => $groupId, 'subscriber_count' => count($subscribers)]);
            return response()->json(['success' => true, 'subscribers' => $subscribers]);
        } catch (\Exception $e) {
            Log::error("Error fetching detailed subscribers: " . $e->getMessage(), ['group_id' => $groupId]);
            return response()->json(['success' => false, 'message' => 'Unable to fetch detailed subscribers.'], 500);
        }
    }

    public function downloadSubscribersCsv()
    {
        Log::info('Starting CSV download process via API.');

        try {
            Log::info('Attempting to retrieve detailed subscribers from MailerLite.');
            $subscribers = $this->mailerLite->getDetailedSubscribers($groupId = null);

            if (is_null($subscribers) || !is_array($subscribers)) {
                Log::warning('No subscribers data was retrieved or invalid data format.');
                $subscribers = [];
            }

            Log::info('Successfully retrieved subscribers. Total count: ' . count($subscribers));

            $headers = [
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=subscribers.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            ];

            $columns = ['ID', 'Email', 'First Name', 'Last Name', 'Start Date', 'Date Updated'];

            $callback = function () use ($subscribers, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach ($subscribers as $index => $subscriber) {
                    $fName = 'N/A';
                    $lName = 'N/A';
                    $startDate = 'N/A';

                    if (isset($subscriber['fields']) && is_array($subscriber['fields'])) {
                        foreach ($subscriber['fields'] as $field) {
                            if ($field['key'] === 'fname') {
                                $fName = $field['value'];
                            } elseif ($field['key'] === 'lname') {
                                $lName = $field['value'];
                            } elseif ($field['key'] === 'start_date') {
                                $startDate = $field['value'];
                            }
                        }
                    }

                    fputcsv($file, [
                        $index + 1,
                        $subscriber['email'] ?? 'N/A',
                        $fName,
                        $lName,
                        $startDate,
                        $subscriber['date_updated'] ?? 'N/A'
                    ]);
                }

                fclose($file);
            };

            return new StreamedResponse($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Error downloading CSV: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to download CSV.'], 500);
        }
    }

    public function updateStartDate(Request $request)
    {
        // Log the beginning of the request
        Log::info('Starting to update the subscriber\'s start date via API.', [
            'request_data' => $request->all()
        ]);

        // Log validation attempt
        Log::info('Validating the request data for start date update.');

        $validatedData = $request->validate([
            'email' => 'required|email',
            'start_date' => 'required|string',
            'country' => 'nullable|string',
        ]);

        // Log the validated data
        Log::info('Request data successfully validated.', [
            'validated_data' => $validatedData
        ]);

        try {
            // Extract the validated inputs
            $email = $request->input('email');
            $newStartDate = $request->input('start_date');
            $country = $request->input('country') ?? 'N/A';

            // Log input data before the update
            Log::info('Attempting to update start date for subscriber.', [
                'email' => $email,
                'new_start_date' => $newStartDate,
                'country' => $country
            ]);

            // Assuming MailerLiteService has a method to update the start date
            $this->mailerLite->updateSubscriberStartDate($email, $country, $newStartDate);

            // Log success of the update
            Log::info('Subscriber\'s start date updated successfully.', [
                'email' => $email,
                'new_start_date' => $newStartDate,
                'country' => $country
            ]);

            return response()->json(['success' => true, 'message' => 'Start date updated successfully!']);

        } catch (\Exception $e) {
            // Log detailed error message and stack trace
            Log::error('Error encountered while updating start date.', [
                'email' => $email ?? 'N/A',
                'new_start_date' => $newStartDate ?? 'N/A',
                'country' => $country ?? 'N/A',
                'exception_message' => $e->getMessage(),
                'stack_trace' => $e->getTraceAsString()
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to update start date.'], 500);
        }
    }
}
