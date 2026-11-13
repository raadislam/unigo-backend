<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyAdminServiceSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('services.unigo_admin.secret');
        $timestamp = (string) $request->header('X-UniGo-Timestamp', '');
        $signature = (string) $request->header('X-UniGo-Signature', '');

        if ($secret === '' || $timestamp === '' || $signature === '' || !ctype_digit($timestamp)) {
            return response()->json(['success' => false, 'message' => 'Unauthorized service request.'], 401);
        }

        if (abs(now()->timestamp - (int) $timestamp) > 300) {
            return response()->json(['success' => false, 'message' => 'Request timestamp expired.'], 401);
        }

        $expected = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

        if (!hash_equals($expected, $signature)) {
            return response()->json(['success' => false, 'message' => 'Invalid service signature.'], 401);
        }

        return $next($request);
    }
}
