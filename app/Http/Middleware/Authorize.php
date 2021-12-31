<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class Authorize
{
    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws AuthorizationException
     */
    public function handle(Request $request, Closure $next)
    {
        $ability = $this->getRouteName($request);

        if (!Auth::user()->can(strtolower($ability))) {
            throw new AuthorizationException();
        }

        return $next($request);
    }

    protected function getRouteName($request)
    {
        $router = $request->route();
        if (is_array($router)) {
            if (isset($router[1]['as'])) {
                return $router[1]['as'];
            }
        }

        return $router->getName();
    }
}
