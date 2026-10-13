<?php

declare(strict_types=1);

namespace App\Actions\Emails;

use App\Ai\Agents\EmailDrafter;
use App\Enum\EmailDirection;
use App\Models\Contact;
use App\Models\Dealership;
use App\Models\DealershipEmail;
use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

/**
 * Asks Claude for an email to a dealership contact, written from the thread so far and the
 * rep's notes. A draft that still contains slop goes back for one revision.
 */
final class DraftDealershipEmail
{
    /** Older messages add little to a reply and only cost tokens. */
    private const int MAX_THREAD_MESSAGES = 12;

    private const int MAX_MESSAGE_LENGTH = 3000;

    public function __construct(private readonly FindEmailSlop $findSlop) {}

    /**
     * @return array{subject: string, body: string, bodyHtml: string}
     */
    public function __invoke(User $rep, Dealership $dealership, ?Contact $contact, ?DealershipEmail $replyingTo, ?string $notes): array
    {
        $prompt = $this->prompt($rep, $dealership, $contact, $replyingTo, $notes);
        $agent = new EmailDrafter;

        $draft = $this->draftFrom($agent->prompt($prompt));
        $problems = ($this->findSlop)($draft['subject']."\n".$draft['body']);

        if ($problems !== []) {
            $draft = $this->draftFrom($agent->prompt($prompt
                ."\n\nYou wrote this draft:\n\nSubject: {$draft['subject']}\n\n{$draft['body']}"
                ."\n\nRevise it. Keep what works and fix only these problems:\n- ".implode("\n- ", $problems)));
        }

        $subject = $replyingTo === null
            ? $draft['subject']
            : 'Re: '.preg_replace('/^(re:\s*)+/i', '', ($replyingTo->parent ?? $replyingTo)->subject);

        return [
            'subject' => Str::limit($subject, 255, ''),
            'body' => $draft['body'],
            'bodyHtml' => $this->toHtml($draft['body']),
        ];
    }

    private function prompt(User $rep, Dealership $dealership, ?Contact $contact, ?DealershipEmail $replyingTo, ?string $notes): string
    {
        $lines = [
            'Rep: '.$rep->name,
            'Dealership: '.implode(', ', array_filter([$dealership->name, $dealership->city, $dealership->state])).($dealership->type ? " ({$dealership->type})" : ''),
        ];

        if ($contact !== null) {
            $lines[] = 'Writing to: '.$contact->name.($contact->position ? ", {$contact->position}" : '');
        }

        if ($replyingTo !== null) {
            $lines[] = '';
            $lines[] = 'Email thread so far, oldest first:';

            foreach ($this->thread($replyingTo) as $message) {
                $author = $message->direction === EmailDirection::Inbound
                    ? ($message->from_name ?: $message->from_email).' (contact)'
                    : ($message->from_name ?: 'Rep').' (rep)';

                $lines[] = '';
                $lines[] = sprintf('--- %s, %s ---', $author, $message->created_at->format('M j, Y g:i A'));
                $lines[] = 'Subject: '.$message->subject;
                $lines[] = Str::limit(mb_trim($message->body_text), self::MAX_MESSAGE_LENGTH);
            }

            $lines[] = '';
            $lines[] = 'Write the rep\'s reply to the latest message.';
        } else {
            $lines[] = '';
            $lines[] = 'This is a new email, not a reply.';
        }

        if ($notes !== null && mb_trim($notes) !== '') {
            $lines[] = '';
            $lines[] = 'Rep\'s notes on what to say: '.mb_trim($notes);
        }

        return implode("\n", $lines);
    }

    /**
     * @return list<DealershipEmail>
     */
    private function thread(DealershipEmail $replyingTo): array
    {
        $threadId = $replyingTo->parent_id ?? $replyingTo->id;

        return DealershipEmail::query()
            ->where(fn ($query) => $query->whereKey($threadId)->orWhere('parent_id', $threadId))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(self::MAX_THREAD_MESSAGES)
            ->get()
            ->reverse()
            ->values()
            ->all();
    }

    /**
     * @return array{subject: string, body: string}
     */
    private function draftFrom(mixed $response): array
    {
        $output = $response instanceof StructuredAgentResponse ? $response->toArray() : [];
        $body = is_string($output['body'] ?? null) ? mb_trim($output['body']) : '';

        if ($body === '') {
            throw new RuntimeException('The email draft came back empty.');
        }

        return [
            'subject' => is_string($output['subject'] ?? null) ? mb_trim($output['subject']) : '',
            'body' => $body,
        ];
    }

    /**
     * Paragraphs for the editor, escaped. The sign-off and name stay together ("Thanks,<br>Joe").
     * The model sometimes separates paragraphs with a single line break, so when there are no
     * blank lines each line becomes its own paragraph.
     */
    private function toHtml(string $body): string
    {
        $body = mb_trim(str_replace("\r\n", "\n", $body));
        $paragraphs = str_contains($body, "\n\n")
            ? preg_split('/\n{2,}/u', $body) ?: []
            : $this->linesAsParagraphs($body);

        return implode('', array_map(
            fn (string $paragraph): string => '<p>'.preg_replace('/\R/u', '<br>', e(mb_trim($paragraph))).'</p>',
            array_filter($paragraphs, fn (string $paragraph): bool => mb_trim($paragraph) !== ''),
        ));
    }

    /**
     * @return list<string>
     */
    private function linesAsParagraphs(string $body): array
    {
        $lines = array_values(array_filter(array_map(mb_trim(...), explode("\n", $body)), fn (string $line): bool => $line !== ''));
        $count = count($lines);

        // "Thanks," followed by a first name is one closing paragraph.
        if ($count >= 2 && str_word_count($lines[$count - 1]) <= 2 && mb_strlen($lines[$count - 2]) <= 20 && str_ends_with($lines[$count - 2], ',')) {
            $lines[$count - 2] .= "\n".array_pop($lines);
        }

        return $lines;
    }
}
