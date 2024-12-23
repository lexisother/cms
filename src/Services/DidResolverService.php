<?php

namespace App\Services;

use GuzzleHttp\Psr7\Uri;
use Illuminate\Support\Facades\Http;

class DidResolverService
{
    public function getDidResponseForDid(string $did)
    {
        TODO("add once caching is a thing");
    }

    public function getHandleFromDid(string $did)
    {
        TODO("add later");
    }

    public function getPdsFromDidResponse(array $res)
    {
        $found = array_filter($res['service'], fn ($item) => $item['id'] === '#atproto_pds');
        if (count($found) === 0) return null;
        return $found[0]['serviceEndpoint'];
    }

    public function resolveDid(string $did)
    {
        $uri = new Uri($did);
        if ($uri->getScheme() !== 'did') return null;

        $segments = explode(':', $uri->getPath());

        return match ($segments[0]) {
            'plc' => $this->resolveThroughPlcDirectory($did),
            'web' => $this->resolveThroughWeb($segments[1]),
            default => null
        };
    }

    public function resolveThroughWeb(string $domain)
    {
        $res = Http::get("https://$domain/.well-known/did.json");
        return $res->json();
    }

    public function resolveThroughPlcDirectory(string $did)
    {
        $res = Http::get("https://plc.directory/$did");
        return $res->json();
    }
}
