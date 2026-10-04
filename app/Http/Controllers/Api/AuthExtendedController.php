<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthExtendedController extends Controller
{
    /**
     * POST /api/auth/forgot-password
     * No mailer in this environment: returns the reset token directly
     * (documented as dev behaviour; swap for a mail send in production).
     */
    public function forgotPassword(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $data['email']],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        return response()->json([
            'message' => 'Password reset token generated.',
            'data' => ['reset_token' => $token],
        ]);
    }

    /** POST /api/auth/reset-password */
    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $row = DB::table('password_reset_tokens')
            ->where('email', $data['email'])
            ->where('token', hash('sha256', $data['token']))
            ->first();

        if (! $row || now()->diffInMinutes($row->created_at) > 60) {
            return response()->json(['message' => 'Reset token is invalid or expired.'], 422);
        }

        $user = User::where('email', $data['email'])->firstOrFail();
        $user->update(['password' => $data['password']]);

        // Invalidate the used token + all existing sessions.
        DB::table('password_reset_tokens')->where('email', $data['email'])->delete();
        $user->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully. Please log in again.']);
    }

    /** POST /api/auth/token/refresh — revoke current token, issue a fresh one. */
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();

        $token = $user->createToken($request->input('device_name', 'mobile-app'))->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed.',
            'data' => ['token' => $token],
        ]);
    }

    /** POST /api/account/deactivate — soft lock + logout everywhere. */
    public function deactivate(Request $request)
    {
        $request->validate(['password' => ['required', 'string']]);

        if (! \Illuminate\Support\Facades\Hash::check($request->input('password'), $request->user()->password)) {
            return response()->json(['message' => 'Password is incorrect.'], 422);
        }

        $user = $request->user();
        $user->update(['is_active' => false]);
        $user->tokens()->delete();

        return response()->json(['message' => 'Account deactivated.']);
    }
}
