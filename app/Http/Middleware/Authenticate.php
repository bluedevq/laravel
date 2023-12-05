<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        $area = getArea();

        if (!getGuard()->check()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => false,
                    'message' => __('messages.not_permission'),
                    'data' => [],
                ], 401);
            }

            return redirect()->route($area . '.login');
        }

        return $next($request);
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
