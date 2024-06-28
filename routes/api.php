<?php

use App\Http\Controllers\Api\MailerLiteApiController;



Route::put('update-course-start-date', [MailerLiteApiController::class, 'updateCourseStartDate']);

Route::get('/subscriber', [MailerLiteApiController::class, 'getSubscriber']);





