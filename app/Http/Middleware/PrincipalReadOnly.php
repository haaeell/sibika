<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrincipalReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->hasRole('kepala_sekolah') && ! $request->user()->hasAnyRole(['bk', 'super_admin'])) {
            $name = $request->route()?->getName() ?? '';
            if ($name !== 'logout') {
                abort_unless($request->isMethod('GET') && (str_starts_with($name, 'bk.') || str_starts_with($name, 'admin.') || in_array($name, ['dashboard', 'home', 'regions.index'], true)), 403);
                abort_if(preg_match('/(counseling|export|download|template|import|create|warning-letter|reset-account|upload|\.documents\.|principals|\.edit$)/', $name)
                    && ! in_array($name, ['admin.account.edit', 'admin.login-settings.edit'], true), 403);
                abort_if($name === 'bk.articles.preview', 403);
                abort_if($request->route()?->parameter('format') !== null || $name === 'regions.index', 403);
            }
        }

        $response = $next($request);
        if ($request->user()?->hasRole('kepala_sekolah') && $request->routeIs('bk.*.data') && $response instanceof JsonResponse) {
            $payload = $response->getData(true);
            foreach ($payload['data'] ?? [] as &$row) {
                if (array_key_exists('action', $row)) {
                    $row['action'] = '';
                }
            }
            unset($row);
            $response->setData($payload);
        }

        return $response;
    }
}
