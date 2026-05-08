<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleDriveStorageService
{
    public function enabled(): bool
    {
        return (bool) config('services.google_drive.enabled')
            && filled(config('services.google_drive.folder_id'))
            && filled(config('services.google_drive.service_account_json'));
    }

    public function upload(UploadedFile $file, string $prefix = 'document'): ?array
    {
        if (!$this->enabled()) {
            return null;
        }

        try {
            $credentials = $this->credentials();
            $accessToken = $this->accessToken($credentials);
            $folderId = (string) config('services.google_drive.folder_id');
            $originalName = $file->getClientOriginalName();
            $safeName = now()->format('Ymd_His') . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
            $extension = $file->getClientOriginalExtension();
            $driveName = $safeName . ($extension ? '.' . $extension : '');
            $mime = $file->getClientMimeType() ?: 'application/octet-stream';

            $metadata = [
                'name' => $driveName,
                'parents' => [$folderId],
                'description' => 'TIMAH ACADEMY - ' . $prefix . ' - ' . $originalName,
            ];

            $boundary = 'timah_' . Str::random(24);
            $body = "--{$boundary}\r\n";
            $body .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
            $body .= json_encode($metadata, JSON_UNESCAPED_UNICODE) . "\r\n";
            $body .= "--{$boundary}\r\n";
            $body .= "Content-Type: {$mime}\r\n\r\n";
            $body .= file_get_contents($file->getRealPath()) . "\r\n";
            $body .= "--{$boundary}--";

            $upload = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'multipart/related; boundary=' . $boundary])
                ->timeout(120)
                ->send('POST', 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&fields=id,name,webViewLink,webContentLink', [
                    'body' => $body,
                ]);

            if (!$upload->successful()) {
                Log::warning('Google Drive upload failed', ['status' => $upload->status(), 'body' => $upload->body()]);
                return null;
            }

            $uploaded = $upload->json();
            $fileId = $uploaded['id'] ?? null;
            if (!$fileId) {
                return null;
            }

            $this->makePublic($accessToken, $fileId);

            return [
                $prefix . '_drive_id' => $fileId,
                $prefix . '_drive_url' => 'https://drive.google.com/uc?export=download&id=' . $fileId,
            ];
        } catch (Throwable $e) {
            Log::error('Google Drive upload exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return null;
        }
    }

    private function credentials(): array
    {
        $json = (string) config('services.google_drive.service_account_json');
        $json = trim($json);

        if (str_starts_with($json, base_path()) && file_exists($json)) {
            $json = file_get_contents($json) ?: '';
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            $decoded = json_decode(stripslashes($json), true);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException('Configuration Google Drive invalide.');
        }

        return $decoded;
    }

    private function accessToken(array $credentials): string
    {
        $now = time();
        $header = ['alg' => 'RS256', 'typ' => 'JWT'];
        $claim = [
            'iss' => $credentials['client_email'] ?? '',
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'aud' => $credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ];

        $segments = [
            $this->base64UrlEncode(json_encode($header)),
            $this->base64UrlEncode(json_encode($claim)),
        ];

        $signingInput = implode('.', $segments);
        $signature = '';
        $privateKey = $credentials['private_key'] ?? '';
        if (!openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            throw new \RuntimeException('Signature Google Drive impossible.');
        }

        $jwt = $signingInput . '.' . $this->base64UrlEncode($signature);

        $response = Http::asForm()->timeout(45)->post($credentials['token_uri'] ?? 'https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Token Google Drive refusé : ' . $response->body());
        }

        return (string) $response->json('access_token');
    }

    private function makePublic(string $accessToken, string $fileId): void
    {
        Http::withToken($accessToken)
            ->timeout(30)
            ->post('https://www.googleapis.com/drive/v3/files/' . $fileId . '/permissions', [
                'role' => 'reader',
                'type' => 'anyone',
            ]);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
