<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Dealership;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    private string $apiKey;
    private string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->apiUrl = config('services.openai.url', 'https://api.openai.com/v1/chat/completions');
    }

    public function generateResponse(string $message, string $sessionId, int $userId): array
    {
        // If no API key, return mock response
        if (empty($this->apiKey)) {
            \Log::info('No API key found, using mock response');
            $mockResponse = $this->getMockResponse($message);
            $this->storeMessages($sessionId, $userId, $message, $mockResponse['response'], []);
            return $mockResponse;
        }

        $context = $this->buildCrmContext($userId);
        $chatHistory = $this->getChatHistory($sessionId, $userId);

        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($context)
            ],
            ...$chatHistory,
            [
                'role' => 'user',
                'content' => $message
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->timeout(30)->post($this->apiUrl, [
                'model' => 'gpt-4',
                'messages' => $messages,
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            if ($response->successful()) {
                $aiResponse = $response->json('choices.0.message.content');
                $this->storeMessages($sessionId, $userId, $message, $aiResponse, $context);

                return [
                    'success' => true,
                    'response' => $aiResponse,
                ];
            }

            Log::error('AI API Error', ['response' => $response->body()]);
            $mockResponse = $this->getMockResponse($message);
            $this->storeMessages($sessionId, $userId, $message, $mockResponse['response'], $context);
            return $mockResponse;

        } catch (\Exception $e) {
            Log::error('AI Service Exception', [
                'error' => $e->getMessage(),
                'api_key_exists' => !empty($this->apiKey),
                'api_url' => $this->apiUrl
            ]);
            $mockResponse = $this->getMockResponse($message);
            $this->storeMessages($sessionId, $userId, $message, $mockResponse['response'], $context);
            return $mockResponse;
        }
    }

    private function getMockResponse(string $message): array
    {
        $responses = [
            "I'm your CRM assistant! I can help you with dealership information, contacts, and system navigation.",
            "Thanks for your message! I can assist with CRM-related questions and tasks.",
            "I can provide information about your assigned dealerships, recent contacts, and help with CRM tasks.",
            "Let me know what specific information you need about your dealerships or contacts!",
        ];

        return [
            'success' => true,
            'response' => $responses[array_rand($responses)],
        ];
    }

    private function storeMessages(string $sessionId, int $userId, string $userMessage, string $aiResponse, array $context): void
    {
        // Store user message
        Chat::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'message' => ['content' => $userMessage],
            'role' => 'user',
            'context' => $context,
        ]);

        // Store AI response
        Chat::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'message' => ['content' => $aiResponse],
            'role' => 'assistant',
        ]);
    }

    private function buildCrmContext(int $userId): array
    {
        $user = auth()->user();

        $dealerships = Dealership::whereHas('users', static function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['contacts' => function ($query) {
            $query->latest();
        }, 'progresses' => function ($query) {
            $query->latest()->limit(5);
        }])->get();

        $recentContacts = Contact::whereIn('dealership_id', $dealerships->pluck('id'))
            ->latest()
            ->limit(10)
            ->get();

        // Additional data for reporting
        $dealershipsByType = $dealerships->groupBy('type')->map->count();
        $dealershipsByState = $dealerships->groupBy('state')->map->count();
        $developmentDealerships = $dealerships->where('in_development', true);
        $recentProgress = $dealerships->flatMap->progresses->sortByDesc('created_at')->take(10);

        return [
            'user_name' => $user->name,
            'user_role' => $user->getRoleNames()->first(),
            'assigned_dealerships_count' => $dealerships->count(),
            'recent_dealerships' => $dealerships->take(5)->map(function ($dealership) {
                return [
                    'name' => $dealership->name,
                    'type' => $dealership->type,
                    'state' => $dealership->state,
                    'rating' => $dealership->rating,
                    'in_development' => $dealership->in_development,
                    'contacts_count' => $dealership->contacts->count(),
                    'contacts' => $dealership->contacts->take(3)->map(function ($contact) {
                        return [
                            'name' => $contact->name,
                            'email' => $contact->email,
                            'phone' => $contact->phone,
                            'position' => $contact->position,
                            'primary_contact' => $contact->primary_contact,
                        ];
                    })->toArray(),
                    'recent_progress' => $dealership->progresses->take(2)->pluck('notes')->toArray(),
                ];
            })->toArray(),
            'recent_contacts_count' => $recentContacts->count(),
            'system_stats' => [
                'total_dealerships' => Dealership::count(),
                'development_dealerships' => Dealership::where('in_development', true)->count(),
                'total_contacts' => Contact::count(),
            ],
            'reporting_data' => [
                'dealerships_by_type' => $dealershipsByType->toArray(),
                'dealerships_by_state' => $dealershipsByState->toArray(),
                'development_dealerships_count' => $developmentDealerships->count(),
                'development_dealerships' => $developmentDealerships->take(5)->map(function ($dealership) {
                    return [
                        'name' => $dealership->name,
                        'type' => $dealership->type,
                        'state' => $dealership->state,
                        'dev_status' => $dealership->dev_status?->value,
                        'contacts_count' => $dealership->contacts->count(),
                    ];
                })->toArray(),
                'recent_progress' => $recentProgress->map(function ($progress) {
                    return [
                        'dealership_name' => $progress->dealership->name ?? 'Unknown',
                        'notes' => $progress->notes,
                        'created_at' => $progress->created_at->format('Y-m-d H:i'),
                    ];
                })->toArray(),
            ],
        ];
    }

    private function buildSystemPrompt(array $context): string
    {
        $dealershipsInfo = '';
        if (!empty($context['recent_dealerships'])) {
            $dealershipsInfo = "\n\nYour currently assigned dealerships:\n";
            foreach ($context['recent_dealerships'] as $dealership) {
                $dealershipsInfo .= "- {$dealership['name']} ({$dealership['type']}) in {$dealership['state']}\n";
                $dealershipsInfo .= "  Contacts: {$dealership['contacts_count']} total\n";

                if (!empty($dealership['contacts'])) {
                    foreach ($dealership['contacts'] as $contact) {
                        $primary = $contact['primary_contact'] ? ' (Primary)' : '';
                        $dealershipsInfo .= "    • {$contact['name']}{$primary} - {$contact['position']}\n";
                        if ($contact['email']) {
                            $dealershipsInfo .= "      Email: {$contact['email']}\n";
                        }
                        if ($contact['phone']) {
                            $dealershipsInfo .= "      Phone: {$contact['phone']}\n";
                        }
                    }
                }

                if (!empty($dealership['recent_progress'])) {
                    $dealershipsInfo .= "  Recent notes: " . implode('; ', $dealership['recent_progress']) . "\n";
                }
                $dealershipsInfo .= "\n";
            }
        }

        return "You are an AI assistant integrated into a CRM system managing dealerships (automotive, RV, motorsports, maritime). You have DIRECT ACCESS to this user's CRM data and should provide specific, helpful information.

IMPORTANT: You have access to real data and should use it to answer questions. Do not tell the user to check other sections - provide the actual information.

Current user: {$context['user_name']} (Role: {$context['user_role']})

CRM Statistics:
- You are assigned to {$context['assigned_dealerships_count']} dealerships
- System total: {$context['system_stats']['total_dealerships']} dealerships ({$context['system_stats']['development_dealerships']} in development)
- System total: {$context['system_stats']['total_contacts']} contacts
- Recent contacts: {$context['recent_contacts_count']} contacts
{$dealershipsInfo}

You can provide information about:
- Specific dealerships assigned to this user
- Contact details and counts
- Development status and progress notes
- General CRM navigation help
- System statistics and overviews
- Generate reports based on the available data

REPORT GENERATION:
When asked to generate reports, create well-formatted, professional reports using the available data. You can create:
- Dealership summary reports
- Development status reports
- Contact analysis reports
- Progress/activity reports
- Custom reports based on specific requirements

Format reports with clear headings, bullet points, and organized sections. Use the actual data from the context to populate report contents.

CSV DOWNLOAD FUNCTIONALITY:
When generating reports, always offer CSV download options by including these download links:
- Dealerships Report: [Download CSV](/chat/download-csv/dealerships)
- Contacts Report: [Download CSV](/chat/download-csv/contacts)
- Development Status Report: [Download CSV](/chat/download-csv/development)
- Progress/Activity Report: [Download CSV](/chat/download-csv/progress)

Include the relevant CSV download link(s) at the end of any report you generate. The links will allow users to download the data in Excel-compatible format.

When asked about dealerships, contacts, or data, provide specific information from the context above. Be helpful and informative using the actual data available.";
    }

    private function getChatHistory(string $sessionId, int $userId): array
    {
        return Chat::where('session_id', $sessionId)
            ->where('user_id', $userId)
            ->latest()
            ->limit(10)
            ->get()
            ->reverse()
            ->map(function ($chat) {
                return [
                    'role' => $chat->role,
                    'content' => $chat->message['content'],
                ];
            })
            ->toArray();
    }

    public function getChatHistoryForFrontend(string $sessionId, int $userId): array
    {
        return Chat::where('session_id', $sessionId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->limit(50)
            ->get()
            ->map(function ($chat) {
                return [
                    'role' => $chat->role,
                    'content' => $chat->message['content'],
                    'timestamp' => $chat->created_at->toISOString(),
                ];
            })
            ->toArray();
    }

    public function generateCsvReport(string $type, int $userId): string
    {
        $context = $this->buildCrmContext($userId);

        switch ($type) {
            case 'dealerships':
                return $this->generateDealershipsCsv($context);
            case 'contacts':
                return $this->generateContactsCsv($context);
            case 'development':
                return $this->generateDevelopmentCsv($context);
            case 'progress':
                return $this->generateProgressCsv($context);
            default:
                throw new \InvalidArgumentException("Invalid CSV report type: {$type}");
        }
    }

    private function generateDealershipsCsv(array $context): string
    {
        $csv = "Dealership Name,Type,State,Rating,In Development,Contact Count,Primary Contact,Primary Email\n";

        foreach ($context['recent_dealerships'] as $dealership) {
            $primaryContact = collect($dealership['contacts'])->firstWhere('primary_contact', true);

            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%d","%s","%s"' . "\n",
                $dealership['name'],
                $dealership['type'],
                $dealership['state'],
                $dealership['rating'] ?? 'N/A',
                $dealership['in_development'] ? 'Yes' : 'No',
                $dealership['contacts_count'],
                $primaryContact['name'] ?? 'N/A',
                $primaryContact['email'] ?? 'N/A'
            );
        }

        return $csv;
    }

    private function generateContactsCsv(array $context): string
    {
        $csv = "Dealership,Contact Name,Email,Phone,Position,Primary Contact\n";

        foreach ($context['recent_dealerships'] as $dealership) {
            foreach ($dealership['contacts'] as $contact) {
                $csv .= sprintf(
                    '"%s","%s","%s","%s","%s","%s"' . "\n",
                    $dealership['name'],
                    $contact['name'],
                    $contact['email'] ?? 'N/A',
                    $contact['phone'] ?? 'N/A',
                    $contact['position'] ?? 'N/A',
                    $contact['primary_contact'] ? 'Yes' : 'No'
                );
            }
        }

        return $csv;
    }

    private function generateDevelopmentCsv(array $context): string
    {
        $csv = "Dealership Name,Type,State,Development Status,Contact Count\n";

        foreach ($context['reporting_data']['development_dealerships'] as $dealership) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%d"' . "\n",
                $dealership['name'],
                $dealership['type'],
                $dealership['state'],
                $dealership['dev_status'] ?? 'N/A',
                $dealership['contacts_count'] ?? 0
            );
        }

        return $csv;
    }

    private function generateProgressCsv(array $context): string
    {
        $csv = "Dealership,Progress Notes,Date\n";

        foreach ($context['reporting_data']['recent_progress'] as $progress) {
            $csv .= sprintf(
                '"%s","%s","%s"' . "\n",
                $progress['dealership_name'],
                str_replace('"', '""', $progress['notes']),
                $progress['created_at']
            );
        }

        return $csv;
    }
}
