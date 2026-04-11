<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotchPayService
{
    protected string $publicKey;
    protected string $privateKey;
    protected string $baseUrl;
    protected string $callbackUrl;

    public function __construct()
    {
        $this->publicKey   = trim((string) config('notchpay.public_key', env('NOTCHPAY_PUBLIC_KEY', '')));
        $this->privateKey  = trim((string) config('notchpay.private_key', env('NOTCHPAY_PRIVATE_KEY', '')));
        $this->baseUrl     = rtrim((string) config('notchpay.base_url', env('NOTCHPAY_BASE_URL', 'https://api.notchpay.co')), '/');
        $this->callbackUrl = trim((string) config('notchpay.callback_url', env('NOTCHPAY_CALLBACK_URL', '')));
    }

    public function initiatePayment(array $data): array
    {
        if ($this->publicKey === '') {
            Log::error('NotchPay init blocked: missing public key.');

            return [
                'success' => false,
                'message' => 'Configuration NotchPay incomplète : NOTCHPAY_PUBLIC_KEY manquante.',
                'status_code' => null,
                'data' => null,
                'authorization_url' => null,
            ];
        }

        try {
            $phone = $this->normalizePhone($data['phone'] ?? null);

            $payload = [
                'amount'      => (int) ($data['amount'] ?? 0),
                'currency'    => (string) ($data['currency'] ?? 'XAF'),
                'reference'   => (string) ($data['reference'] ?? ''),
                'description' => (string) ($data['description'] ?? 'Abonnement TIMAH SCHOOL'),
            ];

            $customer = [];

            if (!empty($data['name'])) {
                $customer['name'] = (string) $data['name'];
            }

            if (!empty($data['email'])) {
                $customer['email'] = (string) $data['email'];
            }

            if (!empty($phone)) {
                $customer['phone'] = $phone;
            }

            if (!empty($customer)) {
                $payload['customer'] = $customer;
            } else {
                if (!empty($data['email'])) {
                    $payload['email'] = (string) $data['email'];
                }

                if (!empty($phone)) {
                    $payload['phone'] = $phone;
                }
            }

            if ($this->callbackUrl !== '') {
                $payload['callback'] = $this->callbackUrl;
            }

            if (!empty($data['channel'])) {
                $payload['locked_channel'] = (string) $data['channel'];
            }

            $payload['locked_country'] = !empty($data['country'])
                ? strtoupper((string) $data['country'])
                : 'CM';

            if (!empty($data['currency'])) {
                $payload['locked_currency'] = (string) $data['currency'];
            }

            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => $this->publicKey,
                ])
                ->post("{$this->baseUrl}/payments", $payload);

            $json = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => data_get($json, 'message', 'Paiement initialisé.'),
                    'status_code' => $response->status(),
                    'data' => $json,
                    'authorization_url' => data_get($json, 'authorization_url'),
                ];
            }

            Log::error('NotchPay initiate error', [
                'status'  => $response->status(),
                'body'    => $response->body(),
                'json'    => $json,
                'payload' => $payload,
            ]);

            return [
                'success' => false,
                'message' => data_get($json, 'message', 'Erreur NotchPay lors de l\'initialisation du paiement.'),
                'status_code' => $response->status(),
                'data' => $json,
                'authorization_url' => data_get($json, 'authorization_url'),
            ];
        } catch (\Throwable $e) {
            Log::error('NotchPay exception on initiate', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status_code' => null,
                'data' => null,
                'authorization_url' => null,
            ];
        }
    }

    public function verifyPayment(string $reference): array
    {
        if ($this->publicKey === '') {
            Log::error('NotchPay verify blocked: missing public key.');

            return [
                'success' => false,
                'message' => 'Configuration NotchPay incomplète : NOTCHPAY_PUBLIC_KEY manquante.',
                'status_code' => null,
                'data' => null,
            ];
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => $this->publicKey,
                ])
                ->get("{$this->baseUrl}/payments/{$reference}");

            $json = $response->json();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => data_get($json, 'message', 'Paiement vérifié.'),
                    'status_code' => $response->status(),
                    'data' => $json,
                ];
            }

            Log::error('NotchPay verify error', [
                'reference' => $reference,
                'status'    => $response->status(),
                'body'      => $response->body(),
                'json'      => $json,
            ]);

            return [
                'success' => false,
                'message' => data_get($json, 'message', 'Erreur NotchPay lors de la vérification du paiement.'),
                'status_code' => $response->status(),
                'data' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error('NotchPay exception on verify', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
                'status_code' => null,
                'data' => null,
            ];
        }
    }

    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        if ($this->privateKey === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $payload, $this->privateKey);

        return hash_equals($expected, $signature);
    }

    public function generateReference(int $userId, int $planId): string
    {
        return 'TMS-' . $userId . '-' . $planId . '-' . time() . '-' . strtoupper(substr(uniqid('', true), -6));
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $phone = preg_replace('/\s+/', '', trim($phone));

        if (str_starts_with($phone, '+')) {
            return $phone;
        }

        if (str_starts_with($phone, '237')) {
            return '+' . $phone;
        }

        if (preg_match('/^[67]\d{8}$/', $phone)) {
            return '+237' . $phone;
        }

        return $phone;
    }
}
