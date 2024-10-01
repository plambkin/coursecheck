<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MailerLiteService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MailerLiteController extends Controller
{
    protected $mailerLite;

    public function __construct(MailerLiteService $mailerLite)
    {
        $this->mailerLite = $mailerLite;
        Log::info("MailerLiteController instantiated.");
    }

    public function getSubscriber(Request $request)
    {
        Log::info("Fetching subscriber.", ['email' => $request->email, 'country' => $request->country]);

        $validatedData = $request->validate([
            'email' => 'required|email',
            'country' => 'required|string',
        ]);

        try {
            // Fetch the subscriber using the provided email and country
            $subscriber = $this->mailerLite->getSubscriberByEmail($validatedData['email'], $validatedData['country']);

            // Debugging to check the structure of the subscriber data

            if ($subscriber) {
                Log::info("Subscriber fetched successfully.", ['email' => $validatedData['email'], 'country' => $validatedData['country']]);

                // Return the view with both subscriber data and country
                return view('subscriberDetails', [
                    'subscriber' => $subscriber, 
                    'country' => $validatedData['country'] // Passing the country separately
                ]);
            } else {
                Log::warning("Subscriber not found.", ['email' => $validatedData['email'], 'country' => $validatedData['country']]);
                return redirect()->back()->with('error', 'Subscriber not found');
            }
        } catch (\Exception $e) {
            Log::error("Error fetching subscriber: " . $e->getMessage(), ['email' => $validatedData['email'], 'country' => $validatedData['country']]);
            return redirect()->back()->with('error', 'Error fetching subscriber');
        }
    }

    public function getGroups()
    {
        Log::info("Fetching groups from MailerLite.");

        try {
            $groups = $this->mailerLite->getGroups();
            Log::info("Groups fetched successfully.", ['groups' => $groups]);
            return view('groups', ['groups' => $groups]); // Return view with groups
        } catch (\Exception $e) {
            Log::error("Error fetching groups: " . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to fetch groups');
        }
    }

    public function createSubscriber(Request $request)
    {
        Log::info("Creating subscriber.", ['group_id' => $request->group_id, 'email' => $request->email]);

        $validatedData = $request->validate([
            'group_id' => 'required',
            'email' => 'required|email',
        ]);

        try {
            $subscriber = $this->mailerLite->createSubscriber($validatedData['group_id'], $validatedData['email']);
            Log::info("Subscriber created successfully.", ['subscriber' => $subscriber]);
            return redirect()->route('groups')->with('status', 'Subscriber added successfully!');
        } catch (\Exception $e) {
            Log::error("Error creating subscriber: " . $e->getMessage(), ['group_id' => $request->group_id, 'email' => $request->email]);
            return redirect()->route('groups')->with('error', 'Unable to add subscriber.');
        }
    }

    public function getSubscribers($groupId)
    {
        Log::info("Fetching subscribers for group.", ['group_id' => $groupId]);

        try {
            $subscribers = $this->mailerLite->getSubscribers($groupId);
            Log::info("Subscribers fetched successfully.", ['group_id' => $groupId, 'subscriber_count' => count($subscribers)]);
            return view('subscribers', ['subscribers' => $subscribers]); // Return view with subscribers
        } catch (\Exception $e) {
            Log::error("Error fetching subscribers: " . $e->getMessage(), ['group_id' => $groupId]);
            return redirect()->back()->with('error', 'Unable to fetch subscribers');
        }
    }

    public function getDetailedSubscribers($groupId)
    {
        Log::info("Fetching detailed subscribers for group.", ['group_id' => $groupId]);

        try {
            $subscribers = $this->mailerLite->getDetailedSubscribers($groupId);
            Log::info("Detailed subscribers fetched successfully.", ['group_id' => $groupId, 'subscriber_count' => count($subscribers)]);
            return view('subscribersDetailed', ['subscribers' => $subscribers]); // Return view with detailed subscribers
        } catch (\Exception $e) {
            Log::error("Error fetching detailed subscribers: " . $e->getMessage(), ['group_id' => $groupId]);
            return redirect()->back()->with('error', 'Unable to fetch subscribers');
        }
    }

    public function downloadSubscribersCsv()
    {
        Log::info('Starting CSV download process.');

        try {
            Log::info('Attempting to retrieve detailed subscribers from MailerLite.');
            $subscribers = $this->mailerLite->getDetailedSubscribers($groupId = null); // Adjust based on your logic

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
            return redirect()->back()->with('error', 'Failed to download CSV.');
        }
    }


     public function updateStartDate(Request $request)
{
    // Log the beginning of the request
    Log::info('Starting to update the subscriber\'s start date.', [
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

        // Redirect back with a success message
        return redirect()->back()->with('status', 'Start date updated successfully!');

    } catch (\Exception $e) {
        // Log detailed error message and stack trace
        Log::error('Error encountered while updating start date.', [
            'email' => $email ?? 'N/A',
            'new_start_date' => $newStartDate ?? 'N/A',
            'country' => $country ?? 'N/A',
            'exception_message' => $e->getMessage(),
            'stack_trace' => $e->getTraceAsString()
        ]);

        // Redirect back with an error message
        return redirect()->back()->with('error', 'Failed to update start date.');
    }
}

}
