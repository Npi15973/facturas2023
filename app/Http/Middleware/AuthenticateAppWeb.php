<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Response;

class AuthenticateAppWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $validSecrets=explode(',',env('ACCEPTED_SECRETS'));
      if(in_array($request->header('Authorization'),$validSecrets)){
        return $next($request);
      }

      abort(403, 'No tienes autorización para ingresar.');
      return response('Unauthorized.', 401);
    }
}
