<x-layouts.storefront title="AI Support — CityMarket">
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">AI Customer Support</h1>
    <p class="text-gray-500 text-sm mb-6">Get instant help with orders, returns, products, and more.</p>

    {{-- Chat area --}}
    <div id="chat-area" class="bg-white rounded-2xl shadow-sm p-6 min-h-[350px] mb-4 space-y-4 max-h-[500px] overflow-y-auto">
        <div class="flex justify-start">
            <div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-3 max-w-[80%] text-sm">
                👋 Hi! I'm your AI shopping assistant. How can I help you today? You can ask about products, track orders, returns, payments, or anything else!
            </div>
        </div>
    </div>

    {{-- Quick questions --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button class="quick-q px-3 py-1.5 bg-rose-50 text-rose-600 rounded-lg text-xs font-medium hover:bg-rose-100" data-q="Track my order">📦 Track Order</button>
        <button class="quick-q px-3 py-1.5 bg-blue-50 text-blue-600 rounded-lg text-xs font-medium hover:bg-blue-100" data-q="How do I return a product?">↩️ Returns</button>
        <button class="quick-q px-3 py-1.5 bg-green-50 text-green-600 rounded-lg text-xs font-medium hover:bg-green-100" data-q="What payment methods are available?">💳 Payments</button>
        <button class="quick-q px-3 py-1.5 bg-amber-50 text-amber-600 rounded-lg text-xs font-medium hover:bg-amber-100" data-q="Show me popular products">🔥 Popular</button>
    </div>

    {{-- Input --}}
    <form id="chat-form" class="flex gap-3">
        <input type="text" id="chat-input" placeholder="Type your message..." 
               class="flex-1 rounded-xl border-gray-300" required autocomplete="off">
        <button type="submit" id="chat-submit" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700">
            Send
        </button>
    </form>
</div>

<script>
const API_BASE = '{{ url("/") }}';
let conversationId = null;

document.getElementById('chat-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const submitBtn = document.getElementById('chat-submit');
    const chatArea = document.getElementById('chat-area');
    const message = input.value.trim();

    if (!message) return;

    // Escape HTML to prevent XSS
    const esc = (str) => { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; };

    // Add user message
    chatArea.innerHTML += `<div class="flex justify-end"><div class="bg-rose-50 text-rose-900 rounded-2xl px-4 py-2 max-w-[80%] text-sm">${esc(message)}</div></div>`;
    input.value = '';
    submitBtn.disabled = true;
    submitBtn.textContent = '...';
    chatArea.scrollTop = chatArea.scrollHeight;

    try {
        @auth
        const resp = await fetch(`${API_BASE}/api/chat/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': 'Bearer ' + (window.API_TOKEN || '')
            },
            body: JSON.stringify({ message, conversation_id: conversationId }),
        });
        const data = await resp.json();
        conversationId = data.conversation_id || conversationId;
        const reply = data.message || data.reply || 'Sorry, I could not understand.';
        @else
        // For guests, use AI search endpoint as fallback
        const resp = await fetch(`${API_BASE}/api/ai/search`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ query: message }),
        });
        const data = await resp.json();
        const reply = data.ai_response || data.message || 'Please login to chat with our AI assistant.';
        @endauth

        chatArea.innerHTML += `<div class="flex justify-start"><div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-2 max-w-[80%] text-sm whitespace-pre-wrap">${esc(reply)}</div></div>`;
    } catch (err) {
        chatArea.innerHTML += `<div class="flex justify-start"><div class="bg-red-50 text-red-600 rounded-2xl px-4 py-2 max-w-[80%] text-sm">Connection error. Please try again.</div></div>`;
    }

    submitBtn.disabled = false;
    submitBtn.textContent = 'Send';
    chatArea.scrollTop = chatArea.scrollHeight;
});

document.querySelectorAll('.quick-q').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('chat-input').value = btn.dataset.q;
        document.getElementById('chat-form').dispatchEvent(new Event('submit'));
    });
});
</script>
</x-layouts.storefront>
