<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Emails\QueueDealershipEmail;
use App\Http\Requests\DealershipEmailStoreRequest;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

/**
 * Emails a rep sends to a dealership's contacts from the CRM.
 */
final class DealershipEmailController extends Controller
{
    public function store(DealershipEmailStoreRequest $request, Dealership $dealership, QueueDealershipEmail $queueEmail): RedirectResponse
    {
        /** @var User $rep */
        $rep = $request->user();
        $parentId = $request->validated('parent_id');

        /** @var list<UploadedFile> $attachments */
        $attachments = array_values(array_filter(
            $request->file('attachments', []),
            fn (mixed $file): bool => $file instanceof UploadedFile,
        ));

        $queueEmail(
            rep: $rep,
            dealership: $dealership,
            contact: Contact::findOrFail($request->validated('contact_id')),
            subject: $request->string('subject')->trim()->value(),
            bodyHtml: $request->sanitizedBody(),
            replyingTo: $parentId === null ? null : DealershipEmail::find($parentId),
            attachments: $attachments,
        );

        return back()->with('success', 'Email is on its way.');
    }

    /**
     * Marks the contact's replies in a thread as read, once a rep has opened it.
     */
    public function markRead(Dealership $dealership, DealershipEmail $email): RedirectResponse
    {
        abort_unless($email->dealership_id === $dealership->id, 404);

        $threadId = $email->parent_id ?? $email->id;

        $dealership->emails()
            ->unreadReplies()
            ->where(fn (Builder $query) => $query->whereKey($threadId)->orWhere('parent_id', $threadId))
            ->update(['read_at' => now()]);

        return back();
    }
}
