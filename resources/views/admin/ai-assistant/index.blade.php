@extends('components.layouts.admin')

@section('title', 'AI Assistant — Admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">AI Admin Assistant</h1>
    <p class="text-gray-500 text-sm mb-6">Ask questions about your store in natural language. Get instant insights.</p>

    {{-- Quick questions --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <button class="quick-q px-4 py-2 bg-rose-50 text-rose-600 rounded-xl text-sm font-medium hover:bg-rose-100" data-q="Show today's sales summary">📊 Today's Sales</button>
        <button class="quick-q px-4 py-2 bg-blue-50 text-blue-600 rounded-xl text-sm font-medium hover:bg-blue-100" data-q="What are the best selling products?">🏆 Best Sellers</button>
        <button class="quick-q px-4 py-2 bg-amber-50 text-amber-600 rounded-xl text-sm font-medium hover:bg-amber-100" data-q="Show low stock alerts">⚠️ Low Stock</button>
        <button class="quick-q px-4 py-2 bg-green-50 text-green-600 rounded-xl text-sm font-medium hover:bg-green-100" data-q="Show this month's revenue">📅 Monthly Revenue</button>
    </div>

    {{-- Chat area --}}
    <div id="chat-area" class="bg-white rounded-2xl shadow-sm p-6 min-h-[300px] mb-4 space-y-4 max-h-[500px] overflow-y-auto">
        <div class="text-center text-gray-400 py-8">
            <div class="text-4xl mb-2">🤖</div>
            <p>Ask me anything about your store!</p>
        </div>
    </div>

    {{-- Input --}}
    <form id="ai-form" class="flex gap-3">
        @csrf
        <input type="text" id="ai-input" name="question" placeholder="Ask about sales, products, users..." 
               class="flex-1 rounded-xl border-gray-300" required>
        <button type="submit" id="ai-submit" class="px-6 py-3 bg-rose-600 text-white rounded-xl font-medium hover:bg-rose-700">
            Ask AI
        </button>
    </form>
</div>

<script>
document.getElementById('ai-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const input = document.getElementById('ai-input');
    const submitBtn = document.getElementById('ai-submit');
    const chatArea = document.getElementById('chat-area');
    const question = input.value.trim();

    if (!question) return;

    // Escape HTML to prevent XSS
    const esc = (str) => { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; };

    // Add user message
    chatArea.innerHTML += `<div class="flex justify-end"><div class="bg-rose-50 text-rose-900 rounded-2xl px-4 py-2 max-w-[80%] text-sm">${esc(question)}</div></div>`;
    input.value = '';
    submitBtn.disabled = true;
    submitBtn.textContent = 'Thinking...';
    chatArea.scrollTop = chatArea.scrollHeight;

    try {
        const formData = new FormData();
        formData.append('question', question);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}');

        const resp = await fetch('{{ route("admin.ai-assistant.query") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ question }),
        });

        const data = await resp.json();
        const answer = data.answer || 'No response';

        chatArea.innerHTML += `<div class="flex justify-start"><div class="bg-gray-100 text-gray-800 rounded-2xl px-4 py-2 max-w-[80%] text-sm whitespace-pre-wrap">${esc(answer)}</div></div>`;
    } catch (err) {
        chatArea.innerHTML += `<div class="flex justify-start"><div class="bg-red-50 text-red-600 rounded-2xl px-4 py-2 max-w-[80%] text-sm">Sorry, an error occurred. Please try again.</div></div>`;
    }

    submitBtn.disabled = false;
    submitBtn.textContent = 'Ask AI';
    chatArea.scrollTop = chatArea.scrollHeight;
});

document.querySelectorAll('.quick-q').forEach(btn => {
    btn.addEventListener('click', () => {
        document.getElementById('ai-input').value = btn.dataset.q;
        document.getElementById('ai-form').dispatchEvent(new Event('submit'));
    });
});
</script>
@endsection
