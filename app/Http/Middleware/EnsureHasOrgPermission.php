<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasOrgPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next, $permission)
    {
        $orgId = $request->route('org') ? $request->route('org')->id : $request->header('X-Org-Id');

        if (! $request->user() || ! $request->user()->hasPermission($orgId, $permission)) {
            return response()->json(['status'=>false,'message'=>'Unauthorized. Missing permission: '.$permission],403);
        }
        return $next($request);
    }

}
