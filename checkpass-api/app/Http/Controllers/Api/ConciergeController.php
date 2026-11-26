<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ConciergeController extends Controller
{
    private const SYSTEM_PROMPT = <<<'PROMPT'
You are the official CheckPass Estate Operations AI Concierge for Nigeria.
CheckPass is an enterprise digital gate access control and automated dues collection platform for residential gated communities, HOAs, and corporate estates in Nigeria (Lagos, Abuja, Port Harcourt, Ibadan).
Key capabilities:
- 4-Second QR code visitor passes AND unique 6-digit Gate Codes shared via WhatsApp/SMS without requiring guests to install an app.
- Offline & online guard scanner software that verifies QR codes, 6-digit Gate Codes, visitor phone numbers, or daily spoken Access Phrases.
- Automated estate dues & levies (security, diesel surcharge, waste) with instant Paystack online payment and 1-click bank transfer receipt verification.
- Move-out asset clearance manifest verifying appliances/furniture to prevent unauthorized tenant departures.
- 1-Tap resident emergency SOS alerting gate security with exact flat unit number.
- Vetted estate artisan directory (electricians, plumbers).
Pricing:
- Boutique: ₦35,000/mo (up to 50 homes)
- Gated Community: ₦75,000/mo (up to 200 homes)
- Enterprise: Custom for 500+ units.
Free 3-unit trial sandbox provisioned in 48 hours.

Answer clearly, warmly, naturally, and concisely using British/Nigerian English. Do not repeat canned link lists unless the user asks for topics. If the user mentions their name, estate name, flat count, or contact info, acknowledge it warmly and let them know an estate onboarding specialist has received their request.
PROMPT;

    /**
     * POST /api/v1/concierge/chat
     */
    public function chat(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => 'required|string|max:600',
        ]);

        $apiKey = trim((string) env('GEMINI_API_KEY', ''));
        if ($apiKey === '') {
            return response()->json([
                'error'   => true,
                'message' => 'AI Concierge API key is not configured.',
            ], 503);
        }

        $isVertexExpress = str_starts_with($apiKey, 'AQ.');
        $endpoint = $isVertexExpress
            ? "https://aiplatform.googleapis.com/v1/publishers/google/models/gemini-2.5-flash:generateContent?key={$apiKey}"
            : "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

        try {
            $res = Http::timeout(15)->post($endpoint, [
                'contents' => [
                    [
                        'role'  => 'user',
                        'parts' => [
                            [
                                'text' => self::SYSTEM_PROMPT . "\n\nVisitor Question: " . $data['message'] . "\nProvide a helpful, natural response:",
                            ],
                        ],
                    ],
                ],
            ]);

            if (! $res->successful()) {
                return response()->json([
                    'error'   => true,
                    'status'  => $res->status(),
                    'message' => $res->json('error.message') ?? 'Upstream AI request failed.',
                ], 502);
            }

            $rawText = (string) ($res->json('candidates.0.content.parts.0.text') ?? '');
            if (trim($rawText) === '') {
                return response()->json([
                    'error'   => true,
                    'message' => 'Empty AI response.',
                ], 502);
            }

            // Safely escape HTML first, then convert basic markdown bold & newlines
            $safe = htmlspecialchars($rawText, ENT_QUOTES, 'UTF-8');
            $formatted = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $safe);
            $formatted = nl2br($formatted ?? $safe, false);

            return response()->json([
                'reply'  => $formatted,
                'source' => 'ai',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error'   => true,
                'message' => 'AI service temporarily unavailable.',
            ], 502);
        }
    }
}
