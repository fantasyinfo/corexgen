<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugPaymentCallback
{

    /**
     * Debug payment callback
     */
    public function handle(Request $request, Closure $next)
    {
        // Extensive logging for debugging
        Log::channel('payment_debug')->info('Payment Callback Debug', [
            'full_url' => $request->fullUrl(),
            'path' => $request->path(),
            'method' => $request->method(),
            'all_input' => $request->all(),
            'query_params' => $request->query(),
            'request_body' => $request->getContent(),
            'headers' => $request->headers->all(),
            'server_params' => $request->server->all(),
        ]);

        return $next($request);
    }
}