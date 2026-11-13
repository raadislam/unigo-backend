<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function csrf(): JsonResponse
    {
        return response()->json(['success' => true]);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:1024'],
        ]);

        $email = Str::lower($validated['email']);
        $key = 'unigo-admin-login:'.Str::lower($email).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'success' => false,
                'message' => 'Too many login attempts. Please try again later.',
            ], 429)->header('Retry-After', (string) RateLimiter::availableIn($key));
        }

        $authenticated = Auth::guard('admin')->attempt([
            'email' => $email,
            'password' => $validated['password'],
            'status' => 'active',
        ]);

        if (! $authenticated) {
            RateLimiter::hit($key, 60);

            return response()->json([
                'success' => false,
                'message' => 'The supplied credentials are invalid.',
            ], 422);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
        ]);
    }

    public function me(): JsonResponse
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || $admin->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => $admin->role,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => true, 'message' => 'Signed out successfully.']);
    }
}
