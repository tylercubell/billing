<?php

namespace tylercubell\Billing\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CanDeleteDiscount
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
        if (Auth::user()->discount()) {
            return $next($request);
        } else {
            return redirect(config('billing.middleware.redirect'))->with([
                'billing_status'  => 'error',
                'billing_type'    => 'discount',
                'billing_message' => 'Your discount cannot be deleted.'
            ]);
        }
    }
}
