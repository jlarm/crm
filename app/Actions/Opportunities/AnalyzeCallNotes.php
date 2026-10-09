<?php

declare(strict_types=1);

namespace App\Actions\Opportunities;

use App\Ai\Agents\CallNoteAnalyst;
use App\Enum\OpportunityStage;
use App\Enum\TaskPriority;
use App\Enum\TaskType;
use App\Models\Contact;
use App\Models\Opportunity;
use Carbon\CarbonImmutable;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

final class AnalyzeCallNotes
{
    /**
     * Ask the AI for suggested updates, then keep only the ones that can be applied.
     *
     * The model's output is untrusted: unknown enum values and malformed dates are
     * dropped, contact ids must belong to this dealership, unchanged stage or rating
     * suggestions are omitted, and "new" contacts already on file are filtered out.
     *
     * @return array{summary: string, tasks: list<array{title: string, type: string, priority: string, dueDate: string|null, contactId: int|null}>, stage: array{value: string, label: string, reason: string}|null, rating: array{value: string, label: string, reason: string}|null, newContacts: list<array{name: string, position: string, email: string, phone: string}>}
     */
    public function __invoke(Opportunity $opportunity, string $notes): array
    {
        $opportunity->loadMissing('dealership.contacts');

        $response = (new CallNoteAnalyst($opportunity))->prompt($notes);
        $output = $response instanceof StructuredAgentResponse ? $response->toArray() : [];

        $contacts = $opportunity->dealership->contacts;
        $contactIds = $contacts->map(fn (Contact $contact): int => $contact->id)->values()->all();

        return [
            'summary' => mb_trim($this->string($output['summary'] ?? null)),
            'tasks' => $this->tasks($output['tasks'] ?? null, $contactIds),
            'stage' => $this->stage($opportunity, $output['stage'] ?? null, $output['stage_reason'] ?? null),
            'rating' => $this->rating($opportunity, $output['rating'] ?? null, $output['rating_reason'] ?? null),
            'newContacts' => $this->newContacts($output['new_contacts'] ?? null, $contacts->all()),
        ];
    }

    /**
     * @param  array<int, int>  $contactIds
     * @return list<array{title: string, type: string, priority: string, dueDate: string|null, contactId: int|null}>
     */
    private function tasks(mixed $tasks, array $contactIds): array
    {
        $result = [];

        foreach (is_array($tasks) ? $tasks : [] as $task) {
            if (! is_array($task)) {
                continue;
            }

            $title = mb_trim($this->string($task['title'] ?? null));
            $type = TaskType::tryFrom($this->string($task['type'] ?? null));

            if ($title === '' || $type === null) {
                continue;
            }

            $contactId = is_numeric($task['contact_id'] ?? null) ? (int) $task['contact_id'] : 0;

            $result[] = [
                'title' => mb_substr($title, 0, 255),
                'type' => $type->value,
                'priority' => (TaskPriority::tryFrom($this->string($task['priority'] ?? null)) ?? TaskPriority::Medium)->value,
                'dueDate' => $this->date($task['due_date'] ?? null),
                'contactId' => in_array($contactId, $contactIds, true) ? $contactId : null,
            ];
        }

        return $result;
    }

    /**
     * @return array{value: string, label: string, reason: string}|null
     */
    private function stage(Opportunity $opportunity, mixed $stage, mixed $reason): ?array
    {
        $suggested = OpportunityStage::tryFrom($this->string($stage));

        if ($suggested === null || $suggested === $opportunity->stage) {
            return null;
        }

        return ['value' => $suggested->value, 'label' => $suggested->getLabel(), 'reason' => mb_trim($this->string($reason))];
    }

    /**
     * @return array{value: string, label: string, reason: string}|null
     */
    private function rating(Opportunity $opportunity, mixed $rating, mixed $reason): ?array
    {
        $suggested = mb_strtolower($this->string($rating));
        $current = mb_strtolower((string) $opportunity->dealership->rating);

        if (! in_array($suggested, ['hot', 'warm', 'cold'], true) || $suggested === $current) {
            return null;
        }

        return ['value' => $suggested, 'label' => ucfirst($suggested), 'reason' => mb_trim($this->string($reason))];
    }

    /**
     * @param  array<int, Contact>  $existing
     * @return list<array{name: string, position: string, email: string, phone: string}>
     */
    private function newContacts(mixed $contacts, array $existing): array
    {
        $knownNames = array_map(fn (Contact $contact): string => mb_strtolower(mb_trim($contact->name)), $existing);
        $knownEmails = array_filter(array_map(fn (Contact $contact): string => mb_strtolower(mb_trim((string) $contact->email)), $existing));

        $result = [];

        foreach (is_array($contacts) ? $contacts : [] as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            $name = mb_trim($this->string($contact['name'] ?? null));
            $email = mb_trim($this->string($contact['email'] ?? null));

            if ($name === '' || in_array(mb_strtolower($name), $knownNames, true)) {
                continue;
            }

            if ($email !== '' && in_array(mb_strtolower($email), $knownEmails, true)) {
                continue;
            }

            $result[] = [
                'name' => $name,
                'position' => mb_trim($this->string($contact['position'] ?? null)),
                'email' => filter_var($email, FILTER_VALIDATE_EMAIL) !== false ? $email : '',
                'phone' => mb_trim($this->string($contact['phone'] ?? null)),
            ];
        }

        return $result;
    }

    private function date(mixed $value): ?string
    {
        $value = $this->string($value);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) {
            return null;
        }

        try {
            $date = CarbonImmutable::createFromFormat('!Y-m-d', $value);
        } catch (Throwable) {
            return null;
        }

        return $date !== null && $date->format('Y-m-d') === $value ? $value : null;
    }

    private function string(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }
}
