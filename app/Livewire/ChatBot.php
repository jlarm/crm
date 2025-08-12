<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Services\AiChatService;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class ChatBot extends Component
{
    public bool $isOpen = false;
    public string $message = '';
    public array $messages = [];
    public bool $isLoading = false;
    public string $sessionId;

    public function mount(): void
    {
        $this->sessionId = session('chat_session_id', Str::uuid()->toString());
        session(['chat_session_id' => $this->sessionId]);

        $this->loadChatHistory();
    }

    public function toggleChat(): void
    {
        $this->isOpen = !$this->isOpen;

        if ($this->isOpen && empty($this->messages)) {
            $this->addMessage('assistant', "Hi {$this->getUser()->name}! I'm your CRM assistant. How can I help you today?");
        }
    }

    public function sendMessage(): void
    {
        if (trim($this->message) === '') {
            return;
        }

        $userMessage = $this->message;
        $this->addMessage('user', $userMessage);
        $this->message = '';
        $this->isLoading = true;

        $this->dispatch('scroll-to-bottom');

        $aiService = app(AiChatService::class);
        $response = $aiService->generateResponse($userMessage, $this->sessionId, $this->getUser()->id);

        $this->isLoading = false;
        $this->addMessage('assistant', $response['response']);

        $this->dispatch('scroll-to-bottom');
    }

    public function clearChat(): void
    {
        $this->messages = [];
        $this->sessionId = Str::uuid()->toString();
        session(['chat_session_id' => $this->sessionId]);

        $this->addMessage('assistant', "Chat cleared! How can I help you?");
    }

    public function render(): View
    {
        return view('livewire.chat-bot');
    }

    private function addMessage(string $role, string $content): void
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content,
            'timestamp' => now()->format('H:i'),
        ];
    }

    private function loadChatHistory(): void
    {
        $chats = Chat::where('session_id', $this->sessionId)
            ->where('user_id', $this->getUser()->id)
            ->latest()
            ->limit(20)
            ->get()
            ->reverse();

        foreach ($chats as $chat) {
            $this->messages[] = [
                'role' => $chat->role,
                'content' => $chat->message['content'],
                'timestamp' => $chat->created_at->format('H:i'),
            ];
        }
    }

    private function getUser()
    {
        return auth()->user();
    }
}
