<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioController;

Route::post('/twilio/incoming', [TwilioController::class, 'incoming']);
Route::get('/twilio/incoming', [TwilioController::class, 'incoming']);
