<?php

namespace tylercubell\Billing\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use tylercubell\Billing\Models\InvoiceModel;

class CanRetrieveInvoice
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
        $find = InvoiceModel::where('id', '=', $request->invoice)
                            ->where('customer', '=', Auth::user()->customer()['id'])
                            ->first();

        if ($find) {
            return $next($request);
        } else {
            return redirect(config('billing.middleware.redirect'))->with([
                'billing_status'  => 'error',
                'billing_type'    => 'card',
                'billing_message' => "The invoice you've requested is not available."
            ]);
        }
    }
}
