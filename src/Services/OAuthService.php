<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OAuthService
{
    public function getProtectedResourceForPds(string $pds)
    {
        $res = Http::get("$pds/.well-known/oauth-protected-resource");
        return $res->json();
    }

    public function getAuthServerDataForAuthServer(string $authServer)
    {
        $res = Http::get("$authServer/.well-known/oauth-authorization-server");
        return $res->json();
    }

    public function getAuthServerDataForPds(string $pds)
    {
        $protectedResource = $this->getProtectedResourceForPds($pds);
        $authServer = $protectedResource['authorization_servers'][0];
        if (!isset($authServer)) return null;
        return $this->getAuthServerDataForAuthServer($authServer);
    }

    public function getClientData()
    {
        $meta = $this->getClientMetadata();
    }

    public function getClientMetadata()
    {
        return [
            'client_id' => "https://{$_SERVER['HTTP_HOST']}/api/oauth/client-metadata.json",
            'client_name' => 'AlyCMS',
            'grant_types' => [
                'authorization_code',
                'refresh_token'
            ],
            'scope' => 'atproto transition:generic',
            'redirect_uris' => [
                "https://{$_SERVER["HTTP_HOST"]}/api/oauth/callback"
            ],
            "dpop_bound_access_tokens" => true,
            "application_type" => "web",
            "token_endpoint_auth_method" => "private_key_jwt",
            "token_endpoint_auth_signing_alg" => "ES256",
            "jwks_uri" => "https://{$_SERVER["HTTP_HOST"]}/api/oauth/jwks.json",
        ];
    }
}