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
