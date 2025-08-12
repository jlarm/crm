<div
    x-data="chatbot()"
    style="position: fixed !important; bottom: 20px !important; right: 20px !important; z-index: 999999 !important; pointer-events: auto !important;"
>
    <!-- Chat Toggle Button -->
    <button
        x-show="!isOpen"
        @click="toggleChat()"
        style="background: #2563eb !important; color: white !important; border-radius: 50% !important; padding: 16px !important; border: none !important; box-shadow: 0 10px 25px rgba(0,0,0,0.3) !important; cursor: pointer !important; transition: all 0.2s !important; visibility: visible !important; opacity: 1 !important; z-index: 999999 !important;"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-75"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-75"
        onmouseover="this.style.transform='scale(1.1)'"
        onmouseout="this.style.transform='scale(1)'"
    >
        <svg style="width: 24px; height: 24px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 4v-4z"></path>
        </svg>
        <div style="position: absolute; top: -4px; right: -4px; width: 12px; height: 12px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
    </button>

    <!-- Chat Window -->
    <div
        x-cloak
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-75 translate-y-4"
        x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 transform scale-75 translate-y-4"
{{--        style="position: absolute; bottom: 80px; right: 0; background: white; border-radius: 12px; box-shadow: 0 20px 50px rgba(0,0,0,0.15); border: 1px solid #e5e7eb; width: 320px; height: 400px; display: flex; flex-direction: column; z-index: 999999; overflow: hidden; max-height: 400px;"--}}
        class="w-full sm:w-96 transition duration z-10 bg-white overflow-hidden rounded-xl shadow-md sm:shadow-xl block"
    >
        <!-- Chat Header -->
        <div style="background: #2563eb; color: white; padding: 16px; display: flex; justify-content: space-between; align-items: center; border-radius: 12px 12px 0 0; flex-shrink: 0; position: relative; z-index: 10;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></div>
                <span style="font-weight: 600; font-size: 16px;">CRM Assistant</span>
            </div>
            <div style="display: flex; gap: 8px;">
                <button
                    @click="clearChat()"
                    style="background: none; border: none; color: #bfdbfe; cursor: pointer; padding: 4px; border-radius: 4px; transition: color 0.2s;"
                    onmouseover="this.style.color='white'"
                    onmouseout="this.style.color='#bfdbfe'"
                    title="Clear chat"
                >
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
                <button
                    @click="toggleChat()"
                    style="background: none; border: none; color: #bfdbfe; cursor: pointer; padding: 4px; border-radius: 4px; transition: color 0.2s;"
                    onmouseover="this.style.color='white'"
                    onmouseout="this.style.color='#bfdbfe'"
                >
                    <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Messages Container -->
        <div
            x-ref="messagesContainer"
            style="flex: 1; overflow-y: scroll; overflow-x: hidden; padding: 16px; background: #f9fafb; min-height: 0; max-height: 280px; height: 280px;"
        >
            <div style="display: flex; flex-direction: column; gap: 12px;"
        >
            <template x-for="(msg, index) in messages" :key="index">
                <div :style="msg.role === 'user' ? 'display: flex; justify-content: flex-end;' : 'display: flex; justify-content: flex-start;'">
                    <div
                        :style="msg.role === 'user' ?
                            'max-width: 240px; padding: 12px !important; border-radius: 12px 12px 4px 12px; font-size: 14px; line-height: 1.4; background: #2563eb; color: white;' :
                            'max-width: 240px; padding: 12px !important; border-radius: 12px 12px 12px 4px; font-size: 14px; line-height: 1.4; background: white; color: #374151; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb;'"
                    >
                        <p style="margin: 0; white-space: pre-wrap;" x-text="msg.content"></p>
                        <span style="font-size: 11px; opacity: 0.7; display: block; margin-top: 4px;" x-text="msg.timestamp"></span>
                    </div>
                </div>
            </template>

            <!-- Loading indicator -->
            <div x-show="isLoading" style="display: flex; justify-content: flex-start;">
                <div style="background: white !important; color: #6b7280; box-shadow: 0 1px 3px rgba(0,0,0,0.1); border: 1px solid #e5e7eb; max-width: 240px; padding: 12px !important; border-radius: 12px 12px 12px 4px;">
                    <div style="display: flex; gap: 4px; align-items: center;">
                        <div style="width: 8px; height: 8px; background: #9ca3af; border-radius: 50%; animation: bounce 1.4s ease-in-out infinite both;"></div>
                        <div style="width: 8px; height: 8px; background: #9ca3af; border-radius: 50%; animation: bounce 1.4s ease-in-out infinite both; animation-delay: 0.16s;"></div>
                        <div style="width: 8px; height: 8px; background: #9ca3af; border-radius: 50%; animation: bounce 1.4s ease-in-out infinite both; animation-delay: 0.32s;"></div>
                    </div>
                </div>
            </div>

            <!-- Bottom spacer for proper scrolling -->
            <div style="height: 60px; width: 100%; flex-shrink: 0;"></div>
            </div>
        </div>

        <!-- Message Input -->
        <div style="padding: 16px; border-top: 1px solid #e5e7eb; background: white; border-radius: 0 0 12px 12px; flex-shrink: 0; position: relative; z-index: 10;">
            <form @submit.prevent="sendMessage()" style="display: flex; gap: 8px;">
                <input
                    x-ref="messageInput"
                    x-model="message"
                    type="text"
                    placeholder="Type your message..."
                    style="flex: 1; border: 1px solid #d1d5db; border-radius: 8px; padding: 12px; font-size: 14px; outline: none; transition: border-color 0.2s, box-shadow 0.2s;"
                    :disabled="isLoading"
                    @keydown.enter="sendMessage()"
                    onfocus="this.style.borderColor='#2563eb'; this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)';"
                    onblur="this.style.borderColor='#d1d5db'; this.style.boxShadow='none';"
                >
                <button
                    type="submit"
                    :style="(isLoading || !message.trim()) ?
                        'background: #2563eb; color: white; padding: 12px 16px; border-radius: 8px; font-size: 14px; border: none; cursor: not-allowed; transition: background 0.2s; display: flex; align-items: center; justify-content: center; min-width: 44px; opacity: 0.5;' :
                        'background: #2563eb; color: white; padding: 12px 16px; border-radius: 8px; font-size: 14px; border: none; cursor: pointer; transition: background 0.2s; display: flex; align-items: center; justify-content: center; min-width: 44px; opacity: 1;'"
                    :disabled="isLoading || !message.trim()"
                    @mouseenter="if (!(isLoading || !message.trim())) $el.style.backgroundColor='#1d4ed8'"
                    @mouseleave="if (!(isLoading || !message.trim())) $el.style.backgroundColor='#2563eb'"
                >
                    <span x-show="!isLoading">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </span>
                    <span x-show="isLoading" style="font-size: 12px;">...</span>
                </button>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes bounce {
  0%, 80%, 100% {
    transform: scale(0);
  } 40% {
    transform: scale(1);
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}
</style>

<script>
function chatbot() {
    return {
        isOpen: false,
        message: '',
        messages: [
            {
                role: 'assistant',
                content: 'Hi! I\'m your CRM assistant. How can I help you today?',
                timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
            }
        ],
        isLoading: false,
        sessionId: localStorage.getItem('chatSessionId') || Math.random().toString(36).substring(2, 15),

        init() {
            if (!localStorage.getItem('chatSessionId')) {
                localStorage.setItem('chatSessionId', this.sessionId);
            }
        },

        toggleChat() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.$nextTick(() => {
                    if (this.$refs.messageInput) {
                        this.$refs.messageInput.focus();
                    }
                    this.scrollToBottom();
                });
            }
        },

        async sendMessage() {
            if (!this.message.trim() || this.isLoading) return;

            const userMessage = this.message.trim();
            this.messages.push({
                role: 'user',
                content: userMessage,
                timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
            });

            this.message = '';
            this.isLoading = true;
            this.scrollToBottom();

            try {
                const response = await fetch('/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: userMessage,
                        session_id: this.sessionId
                    })
                });

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const data = await response.json();
                this.messages.push({
                    role: 'assistant',
                    content: data.response || 'Sorry, I encountered an error.',
                    timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                });

            } catch (error) {
                console.error('Chat error:', error);
                this.messages.push({
                    role: 'assistant',
                    content: 'Sorry, I encountered an error. Please try again.',
                    timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                });
            } finally {
                this.isLoading = false;
                this.scrollToBottom();
                this.$nextTick(() => {
                    if (this.$refs.messageInput) {
                        this.$refs.messageInput.focus();
                    }
                });
            }
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const container = this.$refs.messagesContainer;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            });
        },

        clearChat() {
            this.messages = [
                {
                    role: 'assistant',
                    content: 'Chat cleared! How can I help you?',
                    timestamp: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})
                }
            ];
            this.sessionId = Math.random().toString(36).substring(2, 15);
            localStorage.setItem('chatSessionId', this.sessionId);
        }
    }
}
</script>
