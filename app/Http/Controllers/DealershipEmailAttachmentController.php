<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Dealership;
use App\Models\DealershipEmailAttachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class DealershipEmailAttachmentController extends Controller
{
    public function show(Dealership $dealership, DealershipEmailAttachment $attachment): StreamedResponse
    {
        abort_unless($attachment->email->dealership_id === $dealership->id, 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->filename);
    }
}
