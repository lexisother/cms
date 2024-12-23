<?php

namespace App\Services;

use App\Models\OAuthConfig;
use Exception;
use Firebase\JWT\JWK;

class ECDSAService
{
    public function getJwk()
    {
        $config = $this->getConfigFromDatabase();
        return $this->ecPemToJwk($config->client_private_key);
    }

    public function getConfigFromDatabase()
    {
        $config = OAuthConfig::all()->first();
        if (!$config) {
            $config = $this->createDefault();
            $config->save();
        }

        return $config;
    }

    private function createDefault(): OAuthConfig
    {
        $key = openssl_pkey_new([
            'private_key_bits' => 4096,
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'ec' => [
                'curve_name' => 'prime256v1',
            ]
        ]);
        $privateKey = null;
        openssl_pkey_export($key, $privateKey);
        if ($privateKey === null) throw new Exception("Something went wrong while creating the private key.");

        $publicKey = openssl_pkey_get_details($key)['key'];
        if (!isset($publicKey)) throw new Exception("Something went wrong while creating the public key.");

        $jwk = $this->ecPemToJwk($publicKey);

        return new OAuthConfig([
            'client_private_key' => $privateKey,
            'client_public_key' => $publicKey,
            'key_id' => $jwk['kid']
        ]);
    }

    /** @see https://openid.net/specs/draft-jones-json-web-key-03.html */
    private function ecPemToJwk(string $pem): array
    {
        $key = openssl_pkey_get_private($pem) ?: openssl_pkey_get_public($pem);
        $keyDetails = openssl_pkey_get_details($key);

        $publicKey = $keyDetails['ec'];
        $x = rtrim(strtr(base64_encode($publicKey['x']), '+/', '-_'), '=');
        $y = rtrim(strtr(base64_encode($publicKey['y']), '+/', '-_'), '=');

        $jwk = [
            'kty' => 'EC',
            'crv' => 'P-256',
            'x' => $x,
            'y' => $y,
        ];

        $jwk['kid'] = $this->calculateThumbprint($jwk);

        return $jwk;
    }

    /** @see https://openid.net/specs/draft-jones-json-web-key-03.html */
    private function calculateThumbprint(array $jwk): string
    {
        ksort($jwk);
        $normalized = json_encode($jwk, JSON_UNESCAPED_SLASHES);
        $hash = hash('sha256', $normalized, true);
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}
