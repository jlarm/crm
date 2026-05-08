<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RecordLastLogin
{
    private const string SESSION_KEY = 'last_login_recorded';

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user instanceof User && $request->hasSession() && ! $request->session()->has(self::SESSION_KEY)) {
            $user->forceFill(['last_login_at' => now()])->saveQuietly();
            $request->session()->put(self::SESSION_KEY, true);
        }

        return $next($request);
    }
}
