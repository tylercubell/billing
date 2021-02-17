<?php

namespace tylercubell\Billing\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class CanUpdateSubscription
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
        $status = Auth::user()->subscription()['status'];

        if ($status !== null && $status !== 'canceled') {
            return $next($request);
        } else {
            return redirect(config('billing.middleware.redirect'))->with([
                'billing_status'  => 'error',
                'billing_type'    => 'subscription',
                'billing_message' => 'Your subscription cannot be updated.'
            ]);
        }
    }
}
