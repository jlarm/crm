<?php

declare(strict_types=1);

namespace App\Actions\Dealerships;

use App\Ai\Agents\DealershipFinder;
use Laravel\Ai\Responses\StructuredAgentResponse;

final class FindDealershipOnline
{
    public function __construct(private readonly FindLikelyDuplicateDealerships $findDuplicates) {}

    /**
     * Businesses on the web matching what the rep typed, each checked against the CRM.
     *
     * The model's output is untrusted: values that don't look like a state, zip, phone
     * or URL are dropped rather than passed into the form, and results without a name
     * or repeating an earlier one are skipped.
     *
     * @return list<array{name: string, address: string|null, city: string|null, state: string|null, zip_code: string|null, phone: string|null, website: string|null, sourceUrl: string|null, duplicate: array{reason: string, match: array{id: int, name: string, city: string|null, state: string|null}}|null}>
     */
    public function __invoke(string $name, ?string $city = null, ?string $state = null): array
    {
        $location = implode(', ', array_filter([$city, $state]));
        $prompt = $location === '' ? 'Find this dealership: '.$name : sprintf('Find this dealership: %s, in or near %s', $name, $location);

        $response = (new DealershipFinder)->prompt($prompt);
        $output = $response instanceof StructuredAgentResponse ? $response->toArray() : [];

        $results = [];
        $seen = [];

        foreach (is_array($output['results'] ?? null) ? $output['results'] : [] as $result) {
            if (! is_array($result)) {
                continue;
            }

            $resultName = $this->text($result['name'] ?? null, 255);

            if ($resultName === null) {
                continue;
            }

            $address = $this->text($result['address'] ?? null, 255);
            $key = mb_strtolower($resultName.'|'.$address);

            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $phone = $this->phone($result['phone'] ?? null);

            $results[] = [
                'name' => $resultName,
                'address' => $address,
                'city' => $this->text($result['city'] ?? null, 100),
                'state' => $this->state($result['state'] ?? null),
                'zip_code' => $this->zipCode($result['zip_code'] ?? null),
                'phone' => $phone,
                'website' => $this->url($result['website'] ?? null),
                'sourceUrl' => $this->url($result['source_url'] ?? null),
                'duplicate' => $this->findDuplicates->forCandidate($resultName, $phone),
            ];

            if (count($results) === 5) {
                break;
            }
        }

        return $results;
    }

    private function text(mixed $value, int $maxLength): ?string
    {
        $value = is_string($value) ? mb_trim($value) : '';

        return $value === '' ? null : mb_substr($value, 0, $maxLength);
    }

    private function state(mixed $value): ?string
    {
        $value = mb_strtoupper(mb_trim(is_string($value) ? $value : ''));

        return preg_match('/^[A-Z]{2}$/', $value) === 1 ? $value : null;
    }

    private function zipCode(mixed $value): ?string
    {
        $value = mb_trim(is_string($value) ? $value : '');

        return preg_match('/^\d{5}(-\d{4})?$/', $value) === 1 ? $value : null;
    }

    private function phone(mixed $value): ?string
    {
        $digits = preg_replace('/\D+/', '', is_string($value) ? $value : '') ?? '';

        if (mb_strlen($digits) === 11 && str_starts_with($digits, '1')) {
            $digits = mb_substr($digits, 1);
        }

        if (mb_strlen($digits) !== 10) {
            return null;
        }

        return sprintf('(%s) %s-%s', mb_substr($digits, 0, 3), mb_substr($digits, 3, 3), mb_substr($digits, 6));
    }

    private function url(mixed $value): ?string
    {
        $value = mb_trim(is_string($value) ? $value : '');

        if (filter_var($value, FILTER_VALIDATE_URL) === false || preg_match('#^https?://#i', $value) !== 1) {
            return null;
        }

        return mb_substr($value, 0, 255);
    }
}
