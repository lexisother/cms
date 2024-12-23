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
    public function jwks(
        Request $request,
        ECDSAService $ecdsa,
        DomainResolverService $domainResolver,
        DidResolverService $didResolver,
        OAuthService $oauthService,
    )
    {
        $did = "alyxia.dev";
//        $did = "alt.alyxia.dev";
        if (!str_starts_with($did, "did:"))
            $did = $domainResolver->getDidForDomain($did);

        if ($did === null) {
            return new JsonResponse(['error' => "Could not resolve the DID for $did"]);
        }

        $res = $didResolver->resolveDid($did);
        $pds = $didResolver->getPdsFromDidResponse($res);
        if ($pds === null) {
            return new JsonResponse(['error' => "Could not resolve the PDS for $did"]);
        }

        $authServer = $oauthService->getAuthServerDataForPds($pds);
        $clientData = $oauthService->getClientData();

        $jwk = $ecdsa->getJwk();
        $jwk['key_ops'] = [
            'verify'
        ];

        return new JsonResponse([
            'keys' => [
                $jwk
            ]
        ]);
    }

    public function clientMetadata(Request $request, OAuthService $oauthService)
    {
        return new JsonResponse($oauthService->getClientMetadata($request));
    }
}