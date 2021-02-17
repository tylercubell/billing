<?php

namespace tylercubell\Billing\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CanCreateCard
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if ( ! Auth::user()->card()) {
            return $next($request);
        } else {
            return redirect(config('billing.middleware.redirect'))->with([
                'billing_status'  => 'error',
                'billing_type'    => 'card',
                'billing_message' => 'Your card cannot be created.'
            ]);
        }
    }
}
