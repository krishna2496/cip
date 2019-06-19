<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\Config;
use App\Helpers\{Helpers, DatabaseHelper};
use Closure;
use Firebase\JWT\JWT;
use DB;

class TenantConnectionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Pre-Middleware Action
        $token = $request->get('token');

        if ($token) {
           try {
                 $credentials = JWT::decode($token, env('JWT_SECRET'), ['HS256']);
                 $domain = $credentials->fqdn;
            } catch(\Firebase\JWT\ExpiredException $e) {
                return Helpers::errorResponse(trans('api_error_messages.status_code.HTTP_STATUS_401'), 
                                            trans('api_error_messages.status_type.HTTP_STATUS_TYPE_401'), 
                                            trans('api_error_messages.custom_error_code.ERROR_40014'), 
                                            trans('api_error_messages.custom_error_message.40014'));
            } catch(Exception $e) {
                return Helpers::errorResponse(trans('api_error_messages.status_code.HTTP_STATUS_400'), 
                                            trans('api_error_messages.status_type.HTTP_STATUS_TYPE_400'), 
                                            trans('api_error_messages.custom_error_code.ERROR_40016'), 
                                            trans('api_error_messages.custom_error_message.40016'));
            }
        } else {
            
            // Uncomment below line while testing in apis with front side.
            // $domain = Helpers::getSubDomainFromRequest($request);
            
            // comment below line while testing in apis with front side.
            $domain = env('DEFAULT_TENANT');
        }

        if ($domain !== env('APP_DOMAIN')) {
            $tenant = DB::table('tenant')->select('tenant_id')->where('name', $domain)->whereNull('deleted_at')->first();			
            if (!$tenant) {
                return Helpers::errorResponse(trans('messages.status_code.HTTP_STATUS_FORBIDDEN'), 
                                        trans('messages.status_type.HTTP_STATUS_TYPE_403'), 
                                        trans('messages.custom_error_code.ERROR_40008'), 
                                        trans('messages.custom_error_message.40008'));
            }
            DatabaseHelper::createConnection($tenant->tenant_id);
        }
        $response = $next($request);

        return $response;
    }
}
