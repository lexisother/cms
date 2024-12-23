<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class DomainResolverService
{
    public function getDidForDomain(string $handle): ?string
    {
        $dns = $this->resolveThroughDns($handle);
        if ($dns !== null) return $dns;

        return $this->resolveThroughWellKnown($handle);
    }

    public function resolveThroughDns(string $handle): ?string
    {
        $prelude = "_atproto.";
        $domain = "$prelude$handle";

        $query = dns_get_record($domain, DNS_TXT);
        if (count($query) === 0) return null;

        if (!str_starts_with($query[0]['txt'], 'did=')) {
            return null;
        }

        $res = str_replace('did=', '', $query[0]['txt']);
        // if there's more than one replacement here, something's going wrong anyways
        if (is_array($res)) return null;

        return $res;
    }

    public function resolveThroughWellKnown(string $handle): ?string
    {
        $endpoint = ".well-known/atproto-did";
        try {
            $res = Http::get("https://$handle/$endpoint");
        } catch(Exception $e) {
            return null;
        }
        if ($res->status() !== 200) return null;
        return $res->body();
    }
}
