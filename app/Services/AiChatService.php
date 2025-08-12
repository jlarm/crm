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

        \Log::info('Using OpenAI API with key: ' . substr($this->apiKey, 0, 10) . '...');

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
        })->with(['contacts', 'progresses' => function ($query) {
            $query->latest()->limit(5);
        }])->get();

        $recentContacts = Contact::whereIn('dealership_id', $dealerships->pluck('id'))
            ->latest()
            ->limit(10)
            ->get();

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
                    'recent_progress' => $dealership->progresses->take(2)->pluck('notes')->toArray(),
                ];
            })->toArray(),
            'recent_contacts_count' => $recentContacts->count(),
            'system_stats' => [
                'total_dealerships' => Dealership::count(),
                'development_dealerships' => Dealership::where('in_development', true)->count(),
                'total_contacts' => Contact::count(),
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
                if (!empty($dealership['recent_progress'])) {
                    $dealershipsInfo .= "  Recent notes: " . implode('; ', $dealership['recent_progress']) . "\n";
                }
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
}
