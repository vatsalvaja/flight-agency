<?php

namespace App\Services\Ocr;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Google Document AI implementation of {@see OcrEngineInterface}.
 *
 * Sends the raw document (PDF or image) to a Document AI processor and returns
 * the recognised full text. Authentication uses a service-account JSON key; the
 * short-lived OAuth token is minted locally with openssl (no extra composer
 * dependency) and cached for its lifetime.
 */
class GoogleDocumentAiOcrService implements OcrEngineInterface
{
    public function extractText(string $absolutePath, string $mimeType): string
    {
        $config = config('services.google_document_ai');

        foreach (['project_id', 'location', 'processor_id', 'credentials'] as $key) {
            if (empty($config[$key])) {
                throw new RuntimeException("Google Document AI is not configured: missing '{$key}'.");
            }
        }

        if (! is_file($absolutePath)) {
            throw new RuntimeException('Document file could not be read for OCR.');
        }

        $contents = @file_get_contents($absolutePath);
        if ($contents === false) {
            throw new RuntimeException('Document file could not be read for OCR.');
        }

        $credentials = $this->loadCredentials($config['credentials']);
        $token = $this->fetchAccessToken($credentials);
        $location = $config['location'];

        $endpoint = sprintf(
            'https://%s-documentai.googleapis.com/v1/projects/%s/locations/%s/processors/%s:process',
            $location,
            $config['project_id'],
            $location,
            $config['processor_id']
        );

        $response = Http::withToken($token)
            ->timeout(45)
            ->acceptJson()
            ->post($endpoint, [
                'skipHumanReview' => true,
                'rawDocument' => [
                    'content' => base64_encode($contents),
                    'mimeType' => $mimeType,
                ],
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Document AI request failed with status ' . $response->status() . '.');
        }

        return (string) $response->json('document.text', '');
    }

    /**
     * Load and validate the service-account credentials JSON.
     */
    private function loadCredentials(string $path): array
    {
        $absolute = is_file($path) ? $path : base_path($path);

        if (! is_file($absolute)) {
            throw new RuntimeException('Google Document AI credentials file not found.');
        }

        $decoded = json_decode((string) file_get_contents($absolute), true);

        if (! is_array($decoded) || empty($decoded['client_email']) || empty($decoded['private_key'])) {
            throw new RuntimeException('Google Document AI credentials file is invalid.');
        }

        return $decoded;
    }

    /**
     * Mint (and cache) a cloud-platform OAuth access token from the service account.
     */
    private function fetchAccessToken(array $credentials): string
    {
        $cacheKey = 'google_document_ai_token_' . sha1($credentials['client_email']);

        return Cache::remember($cacheKey, 3300, function () use ($credentials) {
            $now = time();

            $jwt = $this->encodeJwt(
                ['alg' => 'RS256', 'typ' => 'JWT'],
                [
                    'iss' => $credentials['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/cloud-platform',
                    'aud' => 'https://oauth2.googleapis.com/token',
                    'iat' => $now,
                    'exp' => $now + 3600,
                ],
                $credentials['private_key']
            );

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            $accessToken = $response->json('access_token');

            if (! $response->successful() || ! $accessToken) {
                throw new RuntimeException('Unable to obtain a Google access token for Document AI.');
            }

            return $accessToken;
        });
    }

    /**
     * Build a signed RS256 JWT for the Google token endpoint.
     */
    private function encodeJwt(array $header, array $claims, string $privateKey): string
    {
        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claims)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';

        if (! openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Unable to sign the Google authentication token.');
        }

        $segments[] = $this->base64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
