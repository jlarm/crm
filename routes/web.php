<?php

use App\Http\Controllers\MailgunWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Mailgun webhook endpoint (no auth required)
Route::post('/webhooks/mailgun', [MailgunWebhookController::class, 'handleEvent'])
    ->name('mailgun.webhook');

// Email tracking endpoints (no auth required)
Route::get('/track/open/{message_id}', [MailgunWebhookController::class, 'trackOpen'])
    ->name('mailgun.open-track');
Route::get('/track/click/{message_id}', [MailgunWebhookController::class, 'trackClick'])
    ->name('mailgun.click-track');

//Route::get('/mailable', function () {
//   $email = \App\Models\DealerEmail::first();
//
//   return new App\Mail\DealerEmailMail($email);
//});
