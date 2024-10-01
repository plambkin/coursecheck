<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MailerLiteController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', function () {
    return view('welcome');
});


Route::get('/get-subscriber', function () {
    return view('subscriberForm'); // Load the form view
});

Route::post('/get-subscriber', [MailerLiteController::class, 'getSubscriber']); // Handle the form submission


Route::post('/subscriber/update-start-date', [MailerLiteController::class, 'updateStartDate'])->name('subscriber.updateStartDate');


Route::get('/test', function () {
    return view('test');
});



//Route::get('/check-subscriber', [MailerLiteController::class, 'getSubscriber']);
Route::post('/update-start-date', [MailerLiteController::class, 'updateSubscriberStartDate']);
Route::post('/export-csv', [MailerLiteController::class, 'downloadSubscribersCsv']);




Route::get('/subscribers/csv', [MailerLiteController::class, 'downloadSubscribersCsv'])->name('subscribers.csv');


Route::get('/groups', [MailerLiteController::class, 'getGroups'])->name('groups');
Route::post('/subscribers', [MailerLiteController::class, 'createSubscriber'])->name('createSubscriber');
Route::get('/groups/{groupId}/subscribers', [MailerLiteController::class, 'getSubscribers'])->name('subscribers');
Route::get('/groups/{groupId}/detailed-subscribers', [MailerLiteController::class, 'getDetailedSubscribers'])->name('detailedSubscribers');



Route::get('/test-logging', function () {
    Log::debug('This is a debug message.');
    Log::info('This is an info message.');
    Log::warning('This is a warning message.');
    Log::error('This is an error message.');
    Log::critical('This is a critical message.');

    return 'Logging test completed. Check your log file.';
});
