<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RedirectFilamentAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('admin', 'admin/*', 'development', 'development/*')) {
            return redirect('/dashboard');
        }

        return $next($request);
    }
}
