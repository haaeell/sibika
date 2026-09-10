<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RegionController extends Controller
{
    public function __invoke(string $resource, ?string $code = null): JsonResponse
    {
        abort_unless(in_array($resource, ['provinces', 'regencies', 'districts', 'villages'], true), 404);
        abort_unless($resource === 'provinces' || filled($code), 404);

        $cacheKey = 'regions.'.$resource.'.'.($code ?: 'all');
        $url = 'https://wilayah.id/api/'.$resource.($code ? '/'.$code : '').'.json';
        $payload = Cache::remember($cacheKey, now()->addDay(), function () use ($url) {
            return Http::acceptJson()->timeout(10)->get($url)->throw()->json();
        });

        return response()->json($payload);
    }
}
