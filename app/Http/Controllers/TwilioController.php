<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessTwilioMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class TwilioController extends Controller
{
    public function incoming(Request $request): Response
    {
        if ($request->isMethod('get')) {
            return $this->twiml();
        }

        if (config('services.twilio.validate') && ! $this->isValidTwilioSignature($request)) {
            Log::warning('Rejected Twilio webhook with invalid signature');

            return $this->twiml();
        }

        $payload = $request->all();
        $from = (string) ($payload['From'] ?? '');
        $hasMedia = (int) ($payload['NumMedia'] ?? 0) > 0;

        Log::info('Twilio incoming', [
            'method' => $request->method(),
            'ua' => $request->userAgent(),
            'from' => $from,
            'to' => $payload['To'] ?? null,
            'body' => $payload['Body'] ?? null,
            'sid' => $payload['MessageSid'] ?? null,
            'num_media' => $payload['NumMedia'] ?? 0,
        ]);

        if ($from === '' || (($payload['Body'] ?? '') === '' && ! $hasMedia)) {
            Log::warning('Twilio webhook missing From or Body', $payload);

            return $this->twiml();
        }

        ProcessTwilioMessage::dispatch($payload);

        return $this->twiml();
    }

    private function twiml(): Response
    {
        return response(
            '<?xml version="1.0" encoding="UTF-8"?><Response></Response>',
            200
        )->header('Content-Type', 'text/xml');
    }

    private function isValidTwilioSignature(Request $request): bool
    {
        $token = (string) config('services.twilio.token');
        $signature = (string) $request->header('X-Twilio-Signature');

        if ($token === '' || $signature === '') {
            return false;
        }

        $data = $request->fullUrl();
        $params = $request->post();
        ksort($params);

        foreach ($params as $key => $value) {
            $data .= $key.$value;
        }

        $expected = base64_encode(hash_hmac('sha1', $data, $token, true));

        return hash_equals($expected, $signature);
    }
}
