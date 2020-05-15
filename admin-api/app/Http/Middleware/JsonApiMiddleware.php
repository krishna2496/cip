<?php
namespace App\Http\Middleware;

use Closure;
use App\Traits\RestExceptionHandlerTrait;

class JsonApiMiddleware
{
    use RestExceptionHandlerTrait;
    
    const PARSED_METHODS = [
        'POST', 'PUT', 'PATCH'
    ];

    /**
     * Handle an incoming request.
     * @param object $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (in_array($request->getMethod(), self::PARSED_METHODS) && (env('APP_ENV') != 'testing')) {
            if (json_decode($request->getContent(), true) == null) {
                return $this->internalServerError(trans('messages.custom_error_message.ERROR_INVALID_JSON'));
            }
        }
        return $next($request);
    }
}