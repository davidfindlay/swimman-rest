<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class LastActiveMiddleware {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // If last active is later older than 1 minute then update it
        $now = Carbon::now();
        $lastActive = new Carbon($user->last_active);
        $lastPlusMinute = $lastActive->addMinute();
        if ($now > $lastPlusMinute) {
            $user->update([
                'last_active' => $now
            ]);
        }

        return $next($request);
    }
}
