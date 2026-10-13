<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\WebSearch;

/**
 * Searches the web for a dealership by name so the "New dealership" form can be filled in.
 *
 * Runs on Sonnet rather than the app's default model: a rep is waiting on the result.
 * Every field is required and uses "" for "not found", rather than null, so the schema
 * works with strict structured output.
 */
#[Model('claude-sonnet-5-5')]
#[Timeout(90)]
class DealershipFinder implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function instructions(): string
    {
        return <<<'TXT'
You look up US vehicle dealerships (car, RV, motorcycle/powersports, boat/marine) and dealer associations on the web so a sales rep can add one to their CRM.

Search the web for the business the rep describes. Return up to 5 distinct real businesses that match, best match first. Different locations of the same group are separate results. Return an empty list if nothing plausibly matches.

Only report details you actually saw in search results or on the business's own pages. Never guess or construct a phone number, address, zip code or website. Use "" for anything you did not find.

For each result:
- name: the business name as the dealership writes it.
- address: street address only, e.g. "1200 W Ogden Ave".
- city, zip_code: as found.
- state: the two-letter USPS code, e.g. "IL".
- phone: the main sales or front-desk number, formatted 555-555-5555.
- website: the dealership's own site, not a listing or review site.
- source_url: the page where you found the address and phone.
TXT;
    }

    public function tools(): iterable
    {
        return [
            (new WebSearch)->max(3)->location(country: 'US'),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'results' => $schema->array()->items($schema->object(fn (JsonSchema $schema): array => [
                'name' => $schema->string()->required(),
                'address' => $schema->string()->required(),
                'city' => $schema->string()->required(),
                'state' => $schema->string()->required(),
                'zip_code' => $schema->string()->required(),
                'phone' => $schema->string()->required(),
                'website' => $schema->string()->required(),
                'source_url' => $schema->string()->required(),
            ]))->required(),
        ];
    }
}
