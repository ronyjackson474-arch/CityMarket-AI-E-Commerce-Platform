<x-layouts.admin title="Users">
    <h2 class="text-xl font-bold text-ink-900 mb-5">Users</h2>

    <form method="GET" class="flex gap-2 mb-4 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email..."
            class="flex-1 max-w-xs px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
        <select name="role" onchange="this.form.submit()" class="px-3 py-2 border border-ink-200 rounded-lg text-sm focus:border-brand-500 outline-none">
            <option value="">All roles</option>
            <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customers</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admins</option>
        </select>
    </form>

    <div class="bg-white rounded-xl border border-ink-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-ink-50 text-ink-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-5 py-3 font-medium">Name</th>
                    <th class="text-left px-5 py-3 font-medium">Email</th>
                    <th class="text-left px-5 py-3 font-medium">Phone</th>
                    <th class="text-left px-5 py-3 font-medium">Role</th>
                    <th class="text-left px-5 py-3 font-medium">Orders</th>
                    <th class="text-left px-5 py-3 font-medium">Status</th>
                    <th class="text-right px-5 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-ink-100">
                @forelse($users as $user)
                    <tr class="hover:bg-ink-50">
                        <td class="px-5 py-3 font-medium text-ink-800">{{ $user->name }}</td>
                        <td class="px-5 py-3 text-ink-600">{{ $user->email }}</td>
                        <td class="px-5 py-3 text-ink-600">{{ $user->phone ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="text-xs {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }} px-2 py-0.5 rounded-full font-medium uppercase">{{ $user->role }}</span>
                        </td>
                        <td class="px-5 py-3 text-ink-600">{{ $user->orders()->count() }}</td>
                        <td class="px-5 py-3">@if($user->is_active)<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>@else<span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Blocked</span>@endif</td>
                        <td class="px-5 py-3 text-right">
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="inline">
                                    @csrf
                                    <button class="text-xs font-medium {{ $user->is_active ? 'text-red-500' : 'text-green-600' }}">{{ $user->is_active ? 'Block' : 'Activate' }}</button>
                                </form>
                            @else
                                <span class="text-xs text-ink-400">— You —</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-ink-400">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.admin>
