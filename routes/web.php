<?php

declare(strict_types=1);

use App\Http\Controllers\MailgunWebhookController;
use App\Models\PdfAttachment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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

// PDF viewing route (requires authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/pdf/{attachment}', function (PdfAttachment $attachment) {
        // Check if file path exists in record
        if (empty($attachment->file_path)) {
            abort(404, 'No PDF file associated with this attachment');
        }

        // Check if file exists in public storage
        if (! Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'PDF file not found: '.$attachment->file_name.'. The file may have been moved or deleted.');
        }

        // Return the PDF file for viewing in browser
        return Storage::disk('public')->response($attachment->file_path, $attachment->file_name, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$attachment->file_name.'"',
        ]);
    })->name('pdf.view');
});

// Route::get('/mailable', function () {
//   $email = \App\Models\DealerEmail::first();
//
//   return new App\Mail\DealerEmailMail($email);
// });
