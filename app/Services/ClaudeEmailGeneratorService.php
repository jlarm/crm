<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DealerEmailTemplate;
use App\Models\Dealership;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ClaudeEmailGeneratorService
{
    private readonly ?string $apiKey;

    private readonly string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.claude.api_key');
        $this->apiUrl = config('services.claude.api_url', 'https://api.anthropic.com/v1/messages');
    }

    public function generateEmailSubject(Dealership $dealership, ?DealerEmailTemplate $template = null): string
    {
        $prompt = $this->buildSubjectPrompt($dealership, $template);

        $response = $this->callClaudeApi($prompt, 100);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? 'Follow-up: '.$dealership->name : $this->extractContent($response);
    }

    public function generateEmailContent(Dealership $dealership, ?DealerEmailTemplate $template = null): string
    {
        $prompt = $this->buildContentPrompt($dealership, $template);

        $response = $this->callClaudeApi($prompt, 1000);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? $template?->body ?? 'Thank you for your time. Looking forward to connecting soon.' : $this->extractContent($response);
    }

    public function generatePersonalizedMessage(Dealership $dealership, string $context = ''): string
    {
        $prompt = $this->buildPersonalizationPrompt($dealership, $context);

        $response = $this->callClaudeApi($prompt, 500);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? "Hi there! I wanted to reach out regarding your {$dealership->type} dealership." : $this->extractContent($response);
    }

    public function generateFollowUpSuggestions(Dealership $dealership): array
    {
        $prompt = $this->buildFollowUpPrompt($dealership);

        $response = $this->callClaudeApi($prompt, 800);

        $content = $this->extractContent($response);

        if ($content === null || $content === '' || $content === '0') {
            return [
                'Schedule a follow-up call',
                'Send product information',
                'Invite to demo session',
            ];
        }

        // Parse numbered list or bullet points
        $suggestions = preg_split('/\n\d+\.|\n[-•]/', $content);
        $suggestions = array_map('trim', array_filter($suggestions));

        return array_slice($suggestions, 0, 5); // Limit to 5 suggestions
    }

    public function generateEmailSubjectWithContext(string $context): string
    {
        $prompt = $this->buildSubjectPromptWithContext($context);

        $response = $this->callClaudeApi($prompt, 100);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? 'Partnership Opportunity - '.mb_substr($context, 0, 30) : $this->extractContent($response);
    }

    public function generateEmailContentWithContext(string $context, string $tone = 'professional'): string
    {
        $prompt = $this->buildContentPromptWithContext($context, $tone);

        $response = $this->callClaudeApi($prompt, 1000);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? $this->getDefaultTemplateContent($context) : $this->extractContent($response);
    }

    public function generateEmailSubjectWithDealershipContext(Dealership $dealership, string $context, ?DealerEmailTemplate $template = null): string
    {
        $prompt = $this->buildSubjectPromptWithDealershipContext($dealership, $context, $template);

        $response = $this->callClaudeApi($prompt, 100);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? 'Re: '.$dealership->name.' - '.mb_substr($context, 0, 30) : $this->extractContent($response);
    }

    public function generateEmailContentWithDealershipContext(
        Dealership $dealership,
        string $context,
        string $tone = 'professional',
        bool $includeCallToAction = true,
        ?DealerEmailTemplate $template = null
    ): string {
        $prompt = $this->buildContentPromptWithDealershipContext($dealership, $context, $tone, $includeCallToAction, $template);

        $response = $this->callClaudeApi($prompt, 1200);

        return in_array($this->extractContent($response), [null, '', '0'], true) ? $this->getDefaultPersonalizedContent($dealership, $context) : $this->extractContent($response);
    }

    public function isConfigured(): bool
    {
        return $this->apiKey !== null && $this->apiKey !== '' && $this->apiKey !== '0';
    }

    private function buildSubjectPrompt(Dealership $dealership, ?DealerEmailTemplate $template): string
    {
        $dealershipInfo = $this->getDealershipContext($dealership);
        $templateContext = $template instanceof DealerEmailTemplate ? "Base template subject: \"{$template->subject}\"\n" : '';

        return "Generate a compelling email subject line for a business outreach email to a {$dealership->type} dealership.

{$templateContext}Dealership details:
{$dealershipInfo}

Requirements:
- Professional and engaging tone
- Specific to their dealership type
- Under 50 characters
- Action-oriented
- No spam words

Return only the subject line, no quotes or extra text.";
    }

    private function buildContentPrompt(Dealership $dealership, ?DealerEmailTemplate $template): string
    {
        $dealershipInfo = $this->getDealershipContext($dealership);
        $templateContext = $template instanceof DealerEmailTemplate ? "Base template:\n{$template->body}\n\n" : '';

        return "Generate personalized email content for a {$dealership->type} dealership outreach.

{$templateContext}Dealership details:
{$dealershipInfo}

Requirements:
- Professional, consultative tone
- Personalized to their business type and current solution
- Include specific value propositions
- Clear call to action
- 150-300 words
- Use {{contact_name}} placeholder for personalization

Return only the email body content in HTML format.";
    }

    private function buildPersonalizationPrompt(Dealership $dealership, string $context): string
    {
        $dealershipInfo = $this->getDealershipContext($dealership);

        return "Generate a personalized opening message for a {$dealership->type} dealership.

Dealership details:
{$dealershipInfo}

Additional context: {$context}

Requirements:
- Warm, professional tone
- Reference their specific business type
- 1-2 sentences max
- Feel genuine, not templated

Return only the opening message.";
    }

    private function buildFollowUpPrompt(Dealership $dealership): string
    {
        $dealershipInfo = $this->getDealershipContext($dealership);

        return "Generate 3-5 follow-up action suggestions for a {$dealership->type} dealership prospect.

Dealership details:
{$dealershipInfo}

Requirements:
- Specific to their business type and current situation
- Professional business development actions
- Actionable and clear
- Prioritized by potential impact

Format as a numbered list. Return only the list items.";
    }

    private function getDealershipContext(Dealership $dealership): string
    {
        $context = "Name: {$dealership->name}\n";
        $context .= "Type: {$dealership->type}\n";
        $context .= "Location: {$dealership->city}, {$dealership->state}\n";
        $context .= "Rating: {$dealership->rating}\n";

        if ($dealership->current_solution_name) {
            $context .= "Current Solution: {$dealership->current_solution_name}\n";
        }

        if ($dealership->current_solution_use) {
            $context .= "Current Usage: {$dealership->current_solution_use}\n";
        }

        if ($dealership->notes) {
            $context .= 'Notes: '.mb_substr($dealership->notes, 0, 200)."\n";
        }

        return $context.('Development Status: '.$dealership->in_development !== '' ? 'In Development ('.($dealership->dev_status?->getLabel() ?? 'Unknown').')' : 'Not in development');
    }

    private function callClaudeApi(string $prompt, int $maxTokens = 500): ?Response
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ])->timeout(30)->post($this->apiUrl, [
                'model' => 'claude-3-haiku-20240307',
                'max_tokens' => $maxTokens,
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if ($response->successful()) {
                return $response;
            }

            Log::error('Claude API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (Exception $e) {
            Log::error('Claude API exception', [
                'message' => $e->getMessage(),
                'prompt_length' => mb_strlen($prompt),
            ]);

            return null;
        }
    }

    private function extractContent(?Response $response): ?string
    {
        if (! $response instanceof Response) {
            return null;
        }

        $data = $response->json();

        if (! isset($data['content'][0]['text'])) {
            Log::warning('Unexpected Claude API response structure', ['data' => $data]);

            return null;
        }

        return mb_trim($data['content'][0]['text']);
    }

    private function buildSubjectPromptWithContext(string $context): string
    {
        return "Create a specific, compelling subject line about: {$context}

CRITICAL REQUIREMENTS:
- Under 50 characters
- Be SPECIFIC to the context - avoid generic business terms
- Professional but engaging tone
- Reference the actual topic, not generic concepts
- NO generic phrases like 'Partnership Opportunity', 'Business Solutions', etc.

FORBIDDEN PHRASES:
❌ 'Partnership Opportunity'
❌ 'Business Solutions'
❌ 'Streamline Your Operations'
❌ 'Take Your Business Further'

INSTEAD: Be direct about the specific context topic.

EXAMPLES:
If context is 'product demo': 'Demo scheduling for next week?'
If context is 'pricing discussion': 'Pricing questions from yesterday'
If context is 'follow-up': 'Following up on our conversation'

Return ONLY the subject line, no quotes.";
    }

    private function buildContentPromptWithContext(string $context, string $tone): string
    {
        $toneDescription = $this->getToneDescription($tone);

        return "Write a unique, specific email about: {$context}

CRITICAL REQUIREMENTS:
- {$toneDescription} tone
- Be HIGHLY SPECIFIC to the context - no generic business language
- AVOID phrases like: 'innovative solutions', 'achieve goals', 'helping businesses like yours'
- Reference specific aspects of the context provided
- Include {{contact_name}} placeholder
- 150-300 words
- Focus on the actual topic, not generic value propositions

EXAMPLES OF WHAT TO AVOID:
❌ 'We specialize in helping businesses achieve their goals'
❌ 'Through innovative solutions'
❌ 'Streamline operations and increase efficiency'
❌ 'Take your business to the next level'

INSTEAD: Be specific about the actual context and provide concrete details related to what was requested.

Return only the email body content in HTML format.";
    }

    private function buildSubjectPromptWithDealershipContext(Dealership $dealership, string $context, ?DealerEmailTemplate $template): string
    {
        $dealershipInfo = $this->getDealershipContext($dealership);
        $templateContext = $template instanceof DealerEmailTemplate ? "Base template subject: \"{$template->subject}\"\n" : '';

        return "Create a personalized subject line for {$dealership->name} about: {$context}

{$templateContext}Dealership Details:
{$dealershipInfo}

REQUIREMENTS:
- Under 50 characters
- Reference the specific context topic
- Can mention their dealership name or location if relevant
- Be conversational and specific
- AVOID generic business language

FORBIDDEN:
❌ Generic phrases like 'Partnership Opportunity'
❌ 'Solutions for your business'
❌ 'Streamline operations'

GOOD EXAMPLES:
✅ '{$dealership->name} - inventory question'
✅ 'Quick follow-up from yesterday'
✅ 'Re: your {$dealership->type} expansion'
✅ '{$dealership->city} dealership insights'

Focus on the actual context and make it personal to this specific dealership.

Return ONLY the subject line.";
    }

    private function buildContentPromptWithDealershipContext(
        Dealership $dealership,
        string $context,
        string $tone,
        bool $includeCallToAction,
        ?DealerEmailTemplate $template
    ): string {
        $dealershipInfo = $this->getDealershipContext($dealership);
        $templateContext = $template instanceof DealerEmailTemplate ? "Base template:\n{$template->body}\n\n" : '';
        $toneDescription = $this->getToneDescription($tone);
        $ctaRequirement = $includeCallToAction ? 'Include a specific, relevant call to action' : 'End with a soft close';

        return "Write a highly personalized email about: {$context}

{$templateContext}Dealership Information:
{$dealershipInfo}

CRITICAL REQUIREMENTS:
- {$toneDescription} tone
- BE SPECIFIC to {$dealership->name} and their {$dealership->type} business
- Reference their current situation: {$dealership->current_solution_name}
- Focus specifically on: {$context}
- AVOID generic business phrases entirely
- {$ctaRequirement}
- 200-400 words
- Use {{contact_name}} placeholder

FORBIDDEN PHRASES (DO NOT USE):
❌ 'innovative solutions' or 'cutting-edge technology'
❌ 'helping businesses like yours achieve their goals'
❌ 'streamline operations and increase sales'
❌ 'take your business to the next level'
❌ 'we specialize in' or 'we have solutions'

INSTEAD:
✅ Reference their specific dealership type and location
✅ Mention their current solution by name if provided
✅ Be concrete about the context topic
✅ Use industry-specific language for {$dealership->type} dealerships

Write as if you personally know this dealership and are addressing their specific situation.

Return only the email body content in HTML format.";
    }

    private function getToneDescription(string $tone): string
    {
        return match ($tone) {
            'professional' => 'Professional and business-focused',
            'friendly' => 'Warm and approachable yet professional',
            'formal' => 'Formal and traditional business',
            'casual' => 'Conversational and relaxed',
            'consultative' => 'Advisory and solution-focused',
            default => 'Professional and business-focused',
        };
    }

    private function getDefaultTemplateContent(string $context): string
    {
        $templates = [
            "<p>Hello {{contact_name}},</p><p>I wanted to touch base about {$context}. Based on what I've seen in the industry, this is becoming increasingly important for dealerships looking to stay competitive.</p><p>Would you be interested in a brief conversation about your current approach?</p><p>Best,<br>Your Sales Team</p>",

            "<p>Hi {{contact_name}},</p><p>I hope you're doing well. I'm reaching out specifically about {$context}. We've been working with several dealerships on this exact topic recently.</p><p>I'd be happy to share some insights that might be relevant to your situation.</p><p>Best regards,<br>Your Sales Team</p>",

            "<p>Hello {{contact_name}},</p><p>I wanted to follow up on {$context}. This has been a hot topic among dealership owners lately, and I thought you might find some recent developments interesting.</p><p>Are you free for a quick call this week?</p><p>Thanks,<br>Your Sales Team</p>",
        ];

        return $templates[array_rand($templates)];
    }

    private function getDefaultPersonalizedContent(Dealership $dealership, string $context): string
    {
        $templates = [
            "<p>Hello {{contact_name}},</p><p>I hope things are going well at {$dealership->name}. I wanted to reach out about {$context}, particularly as it relates to {$dealership->type} operations in {$dealership->state}.</p><p>I've been working with other {$dealership->type} dealerships on similar topics and thought this might be relevant to your current situation.</p><p>Would you have 15 minutes this week to discuss?</p><p>Best,<br>Your Sales Team</p>",

            "<p>Hi {{contact_name}},</p><p>I hope you're having a great week. I'm reaching out specifically about {$context} for {$dealership->name}. Given your location in {$dealership->city}, {$dealership->state}, I think there might be some opportunities we should discuss.</p><p>When would be a good time for a brief conversation?</p><p>Thanks,<br>Your Sales Team</p>",

            "<p>Hello {{contact_name}},</p><p>I wanted to follow up regarding {$context}. I know that managing a {$dealership->type} dealership comes with unique challenges, and this particular topic has been coming up frequently in my conversations with other owners.</p><p>I'd love to get your perspective and share what I've been seeing in the market.</p><p>Best regards,<br>Your Sales Team</p>",
        ];

        return $templates[array_rand($templates)];
    }
}
