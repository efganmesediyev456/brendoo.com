<?php
namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;


class YouCamService
{
    protected $clientId;
    protected $clientSecret;

    public function __construct()
    {
        $this->clientId = config('services.youcam.client_id');
        $this->clientSecret = config('services.youcam.client_secret');
    }

    public function getIdToken(): string
    {
        $timestamp = round(microtime(true) * 1000);
        $data = "client_id={$this->clientId}&timestamp={$timestamp}";
        
        $pemKey = "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split($this->clientSecret, 64, "\n") .
            "-----END PUBLIC KEY-----";

        $publicKey = openssl_pkey_get_public($pemKey);

        if (!$publicKey) {
            throw new Exception('Invalid public key');
        }

        $encrypted = null;
        $success = openssl_public_encrypt($data, $encrypted, $publicKey);

       
        if (!$success) {
            throw new Exception('Encryption failed');
        }
        return base64_encode($encrypted);
    }

    public function getAccessToken(): string
    {
        $idToken = $this->getIdToken();
        $response = Http::withoutVerifying()->post('https://yce-api-01.perfectcorp.com/s2s/v1.0/client/auth', [
            'client_id' => $this->clientId,
            'id_token' => $idToken
        ]);
        if ($response->status() !== 200) {
            throw new Exception('Authentication failed: ' . $response->body());
        }
        return $response['result']['access_token'];
    }
}
