<x-layouts.storefront title="Notifications — CityMarket">

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-ink-900 flex items-center gap-2">🔔 Notifications</h1>
        @if($notifications->whereNull('read_at')->count())
        <form method="POST" action="{{ route('account.notifications.read-all') }}">
            @csrf
            <button type="submit" class="text-sm text-brand-600 hover:text-brand-700 font-medium">Mark all as read</button>
        </form>
        @endif
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">{{ session('success') }}</div>
    @endif

    @if($notifications->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm p-12 text-center border border-ink-100">
            <div class="text-5xl mb-3">🔕</div>
            <p class="text-ink-400">No notifications yet.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($notifications as $n)
                <div class="bg-white rounded-2xl shadow-sm border border-ink-100 p-5 flex items-start gap-4 {{ $n->read_at ? 'opacity-70' : 'ring-1 ring-brand-100' }}">
                    <div class="w-10 h-10 rounded-full {{ $n->read_at ? 'bg-ink-100' : 'bg-brand-50' }} flex items-center justify-center shrink-0">
                        <span class="text-lg">{{ match($n->type) { 'order_status' => '📦', 'payout' => '💰', default => '📣' } }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-ink-900 text-sm">{{ $n->title }}</h3>
                            @unless($n->read_at)<span class="w-2 h-2 bg-brand-500 rounded-full"></span>@endunless
                        </div>
                        <p class="text-sm text-ink-600 mt-1">{{ $n->body }}</p>
                        <div class="text-xs text-ink-400 mt-2">{{ $n->created_at->diffForHumans() }}</div>
                    </div>
                    @unless($n->read_at)
                    <form method="POST" action="{{ route('account.notifications.read', $n->id) }}">
                        @csrf
                        <button type="submit" class="text-xs text-brand-600 hover:text-brand-700">Mark read</button>
                    </form>
                    @endunless
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $notifications->links() }}</div>
    @endif
</div>

</x-layouts.storefront>
