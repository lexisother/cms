<?php

namespace App\Controllers;

use App\Services\DidResolverService;
use App\Services\DomainResolverService;
use App\Services\ECDSAService;
use App\Services\OAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class OAuthController extends Controller
{
    public function clientMetadata(Request $request)
    {
        return new JsonResponse([
            'client_id' => $request->url(),
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
        ]);
    }
}
