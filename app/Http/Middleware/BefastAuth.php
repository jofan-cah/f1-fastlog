<?php


// ================================================================
// 3. MIDDLEWARE di Laravel 12 (app/Http/Middleware/BefastAuth.php)
// ================================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BefastAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Ambil secret key dari header Authorization atau X-Secret-Key
        $secretKey = $request->bearerToken() ?? $request->header('X-Secret-Key');
        $validKey = config('app.befast_secret_key');

        // Validasi secret key
        if (!$secretKey || $secretKey !== $validKey) {
            Log::warning('Invalid Befast secret key attempt', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'provided_key' => $secretKey ? 'YES' : 'NO'
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid secret key',
                'error_code' => 'INVALID_SECRET_KEY'
            ], 401);
        }

        // Log successful access
        Log::info('Befast API access granted', [
            'endpoint' => $request->path(),
            'method' => $request->method(),
            'ip' => $request->ip()
        ]);

        return $next($request);
    }
}
