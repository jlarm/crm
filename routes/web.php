<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealershipContactController;
use App\Http\Controllers\DealershipController;
use App\Http\Controllers\DealershipOpportunityController;
use App\Http\Controllers\DealershipStoreController;
use App\Http\Controllers\MailgunWebhookController;
use App\Http\Controllers\OpportunityActivityController;
use App\Http\Controllers\SalesDashboardController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Settings\AppearanceController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Http\Controllers\TaskCompleteController;
use App\Http\Controllers\TaskController;
use App\Http\Middleware\HandleInertiaRequests;
use App\Models\PdfAttachment;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhooks/mailgun', [MailgunWebhookController::class, 'handleEvent'])
    ->name('mailgun.webhook');

Route::get('/track/open/{message_id}', [MailgunWebhookController::class, 'trackOpen'])
    ->name('mailgun.open-track');
Route::get('/track/click/{message_id}', [MailgunWebhookController::class, 'trackClick'])
    ->name('mailgun.click-track');

Route::middleware(['auth', HandleInertiaRequests::class])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('sales', SalesDashboardController::class)->name('sales.index');
    Route::get('search', SearchController::class)->name('search');

    Route::get('dealerships/create', [DealershipController::class, 'create'])->name('dealerships.create');
    Route::post('dealerships', [DealershipController::class, 'store'])->name('dealerships.store');
    Route::get('dealerships/{dealership}', [DealershipController::class, 'show'])->name('dealerships.show');
    Route::put('dealerships/{dealership}', [DealershipController::class, 'update'])->name('dealerships.update');

    Route::post('dealerships/{dealership}/opportunities', [DealershipOpportunityController::class, 'store'])->name('dealerships.opportunities.store');
    Route::put('dealerships/{dealership}/opportunities/{opportunity}', [DealershipOpportunityController::class, 'update'])->name('dealerships.opportunities.update');
    Route::delete('dealerships/{dealership}/opportunities/{opportunity}', [DealershipOpportunityController::class, 'destroy'])->name('dealerships.opportunities.destroy');

    Route::post('dealerships/{dealership}/opportunities/{opportunity}/activities', [OpportunityActivityController::class, 'store'])->name('dealerships.opportunities.activities.store');
    Route::delete('dealerships/{dealership}/opportunities/{opportunity}/activities/{activity}', [OpportunityActivityController::class, 'destroy'])->name('dealerships.opportunities.activities.destroy');

    Route::post('dealerships/{dealership}/stores', [DealershipStoreController::class, 'store'])->name('dealerships.stores.store');
    Route::put('dealerships/{dealership}/stores/{store}', [DealershipStoreController::class, 'update'])->name('dealerships.stores.update');
    Route::delete('dealerships/{dealership}/stores/{store}', [DealershipStoreController::class, 'destroy'])->name('dealerships.stores.destroy');

    Route::post('dealerships/{dealership}/contacts', [DealershipContactController::class, 'store'])->name('dealerships.contacts.store');
    Route::put('dealerships/{dealership}/contacts/{contact}', [DealershipContactController::class, 'update'])->name('dealerships.contacts.update');
    Route::delete('dealerships/{dealership}/contacts/{contact}', [DealershipContactController::class, 'destroy'])->name('dealerships.contacts.destroy');

    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy');
    Route::patch('tasks/{task}/complete', TaskCompleteController::class)->name('tasks.complete');

    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        Route::get('security', [SecurityController::class, 'edit'])->name('security.edit');
        Route::put('security', [SecurityController::class, 'update'])->name('security.update');

        Route::get('appearance', [AppearanceController::class, 'edit'])->name('appearance.edit');
    });

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
