<?php

use App\Http\Controllers\Api\MailerLiteAPIController;
use Illuminate\Support\Facades\Route;

// Get a subscriber by email and country
Route::post('/get-subscriber', [MailerLiteAPIController::class, 'getSubscriber']);

// Get all groups
Route::get('/groups', [MailerLiteAPIController::class, 'getGroups']);

// Create a new subscriber
Route::post('/create-subscriber', [MailerLiteAPIController::class, 'createSubscriber']);

// Get subscribers of a specific group
Route::get('/groups/{groupId}/subscribers', [MailerLiteAPIController::class, 'getSubscribers']);

// Get detailed subscribers of a specific group
Route::get('/groups/{groupId}/subscribers-detailed', [MailerLiteAPIController::class, 'getDetailedSubscribers']);

// Download subscribers as a CSV
Route::get('/subscribers/download-csv', [MailerLiteAPIController::class, 'downloadSubscribersCsv']);

// Update subscriber's start date
Route::post('/update-start-date', [MailerLiteAPIController::class, 'updateStartDate']);






