<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForceRequestType {

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $contentTypeHeader = strtolower($request->header('Content-Type'));
        if ( ! in_array($contentTypeHeader, ['', 'application/json'])) {
            $request->headers->set('Accept', ['application/json']); // We need this to set the response to JSON.
            return abort(400, 'the requested content type is not supported');
        }

        $acceptHeader = $request->header('Accept');
        if ( ! in_array($acceptHeader, ['*/*', 'application/json'])) {
            $request->headers->set('Accept', ['application/json']); // We need this to set the response to JSON.
            return abort(400, 'this content type is not supported');
        }

        // Always response in this format.
        $request->headers->set('Accept', ['application/json']);

        return $next($request);
    }
}
