<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->isAdmin() && $user->id === auth()->id()) {
            return back()->with('error', 'You cannot deactivate your own admin account.');
        }

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', "User {$user->name} " . ($user->is_active ? 'activated' : 'deactivated') . '.');
    }
}
