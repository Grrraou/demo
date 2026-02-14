/**
 * Chat Example with Laravel Echo + Reverb
 * 
 * This minimal example demonstrates how to:
 * 1. Connect to Reverb WebSocket server
 * 2. Subscribe to a private conversation channel
 * 3. Listen for new messages
 * 
 * Install dependencies:
 *   npm install laravel-echo pusher-js
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Make Pusher available globally (required by Echo)
window.Pusher = Pusher;

// Initialize Laravel Echo with Reverb
const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
});

/**
 * Subscribe to a conversation channel and listen for messages
 * @param {number} conversationId - The conversation ID to subscribe to
 * @param {function} onMessage - Callback when a new message is received
 * @returns {object} - The channel subscription
 */
export function subscribeToConversation(conversationId, onMessage) {
    return echo
        .private(`conversation.${conversationId}`)
        .listen('.message.sent', (data) => {
            console.log('New message received:', data);
            onMessage(data);
        });
}

/**
 * Unsubscribe from a conversation channel
 * @param {number} conversationId - The conversation ID to unsubscribe from
 */
export function unsubscribeFromConversation(conversationId) {
    echo.leave(`conversation.${conversationId}`);
}

/**
 * Send a message via API
 * @param {number} conversationId - The conversation ID
 * @param {string} body - The message body
 * @returns {Promise} - API response
 */
export async function sendMessage(conversationId, body) {
    const response = await fetch(`/api/chat/conversations/${conversationId}/messages`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'include',
        body: JSON.stringify({ body }),
    });

    if (!response.ok) {
        throw new Error(`Failed to send message: ${response.statusText}`);
    }

    return response.json();
}

/**
 * Fetch conversations for the current user
 * @returns {Promise} - List of conversations
 */
export async function fetchConversations() {
    const response = await fetch('/api/chat/conversations', {
        headers: {
            'Accept': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'include',
    });

    if (!response.ok) {
        throw new Error(`Failed to fetch conversations: ${response.statusText}`);
    }

    return response.json();
}

/**
 * Fetch messages for a conversation
 * @param {number} conversationId - The conversation ID
 * @param {object} options - Query options (before, limit)
 * @returns {Promise} - List of messages
 */
export async function fetchMessages(conversationId, options = {}) {
    const params = new URLSearchParams();
    if (options.before) params.set('before', options.before);
    if (options.limit) params.set('limit', options.limit);

    const url = `/api/chat/conversations/${conversationId}/messages?${params}`;
    const response = await fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'include',
    });

    if (!response.ok) {
        throw new Error(`Failed to fetch messages: ${response.statusText}`);
    }

    return response.json();
}

/**
 * Create a new conversation
 * @param {object} data - Conversation data
 * @returns {Promise} - Created conversation
 */
export async function createConversation(data) {
    const response = await fetch('/api/chat/conversations', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-XSRF-TOKEN': getCsrfToken(),
        },
        credentials: 'include',
        body: JSON.stringify(data),
    });

    if (!response.ok) {
        throw new Error(`Failed to create conversation: ${response.statusText}`);
    }

    return response.json();
}

/**
 * Get CSRF token from cookies (for Sanctum)
 */
function getCsrfToken() {
    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
    return match ? decodeURIComponent(match[1]) : '';
}

// Export Echo instance for advanced usage
export { echo };

/* 
 * =====================================================
 * USAGE EXAMPLE
 * =====================================================
 * 
 * // In your Vue/React component or vanilla JS:
 * 
 * import { 
 *     subscribeToConversation, 
 *     unsubscribeFromConversation,
 *     sendMessage,
 *     fetchConversations,
 *     fetchMessages 
 * } from './chat-example';
 * 
 * // 1. Fetch user's conversations
 * const { data: conversations } = await fetchConversations();
 * 
 * // 2. Subscribe to a conversation
 * const conversationId = conversations[0].id;
 * 
 * subscribeToConversation(conversationId, (message) => {
 *     // Handle incoming message
 *     console.log(`${message.sender.name}: ${message.body}`);
 *     
 *     // Update your UI here
 *     // e.g., append message to chat window
 * });
 * 
 * // 3. Fetch existing messages
 * const { data: messages } = await fetchMessages(conversationId);
 * 
 * // 4. Send a message
 * await sendMessage(conversationId, 'Hello!');
 * 
 * // 5. Cleanup when leaving the chat
 * unsubscribeFromConversation(conversationId);
 * 
 * =====================================================
 * CREATE CONVERSATIONS
 * =====================================================
 * 
 * // Direct (1:1) conversation
 * const direct = await createConversation({
 *     type: 'direct',
 *     user_id: 42,
 * });
 * 
 * // Group conversation
 * const group = await createConversation({
 *     type: 'group',
 *     user_ids: [1, 2, 3],
 * });
 * 
 * // Entity-bound conversation (e.g., ticket #123)
 * const ticketChat = await createConversation({
 *     type: 'entity',
 *     entity_type: 'ticket',
 *     entity_id: 123,
 *     user_ids: [1, 2],
 * });
 */
