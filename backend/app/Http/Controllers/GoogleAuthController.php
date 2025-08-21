<?php

namespace App\Http\Controllers;

use App\Models\GoogleToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class GoogleAuthController extends Controller
{
    /**
     * Return OAuth URL for Google consent screen.
     */
    public function getAuthUrl(Request $request)
    {
        $user = $request->user();

        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        $client->setScopes([
            'https://www.googleapis.com/auth/spreadsheets.readonly',
            'email',
            'profile',
        ]);

        // Encode user id in state (signed)
        $state = Crypt::encryptString(json_encode(['uid' => $user->id]));
        $client->setState($state);

        return response()->json([
            'success' => true,
            'auth_url' => $client->createAuthUrl()
        ]);
    }

    /**
     * OAuth callback (must be under web middleware to allow redirects).
     */
    public function callback(Request $request)
    {
        try {
            $code = $request->query('code');
            $state = $request->query('state');
            if (!$code || !$state) {
                return redirect(config('app.frontend_url', 'http://localhost:3000') . '/teacher/import?google=error');
            }

            $payload = json_decode(Crypt::decryptString($state), true);
            $userId = $payload['uid'] ?? null;
            if (!$userId) {
                return redirect(config('app.frontend_url', 'http://localhost:3000') . '/teacher/import?google=error_state');
            }

            $client = new \Google_Client();
            $client->setClientId(config('services.google.client_id'));
            $client->setClientSecret(config('services.google.client_secret'));
            $client->setRedirectUri(config('services.google.redirect'));
            $token = $client->fetchAccessTokenWithAuthCode($code);

            if (isset($token['error'])) {
                Log::error('Google OAuth error: ' . $token['error']);
                return redirect(config('app.frontend_url', 'http://localhost:3000') . '/teacher/import?google=error_token');
            }

            GoogleToken::updateOrCreate(
                ['user_id' => $userId],
                [
                    'access_token' => json_encode($token),
                    'refresh_token' => $token['refresh_token'] ?? null,
                    'expires_at' => now()->addSeconds($token['expires_in'] ?? 3600),
                ]
            );

            return redirect(config('app.frontend_url', 'http://localhost:3000') . '/teacher/import?google=connected');
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback exception: ' . $e->getMessage());
            return redirect(config('app.frontend_url', 'http://localhost:3000') . '/teacher/import?google=exception');
        }
    }

    /**
     * Connection status for current user.
     */
    public function status(Request $request)
    {
        $exists = GoogleToken::where('user_id', $request->user()->id)->exists();
        return response()->json(['success' => true, 'connected' => $exists]);
    }

    /**
     * Preview a Google Sheet (first N rows) by sheet ID and optional sheet name/range.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'sheet_id' => 'required|string',
            'range' => 'nullable|string',
            'sheet_name' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:200'
        ]);

        $token = GoogleToken::where('user_id', $request->user()->id)->first();
        if (!$token) {
            return response()->json(['success' => false, 'message' => 'Google not connected'], 422);
        }

        $client = new \Google_Client();
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setRedirectUri(config('services.google.redirect'));
        $client->setAccessToken(json_decode($token->access_token, true));

        if ($client->isAccessTokenExpired() && $token->refresh_token) {
            $client->fetchAccessTokenWithRefreshToken($token->refresh_token);
            $newToken = $client->getAccessToken();
            $token->access_token = json_encode($newToken);
            $token->expires_at = now()->addSeconds($newToken['expires_in'] ?? 3600);
            $token->save();
        }

        $service = new \Google_Service_Sheets($client);
        $range = $request->input('range');
        $sheetName = $request->input('sheet_name');
        if (!$range) {
            $range = ($sheetName ?: 'Sheet1') . '!A1:Z1000';
        }

        $resp = $service->spreadsheets_values->get($request->input('sheet_id'), $range);
        $values = $resp->getValues();
        if (!$values || count($values) === 0) {
            return response()->json(['success' => true, 'headers' => [], 'rows' => []]);
        }
        $headers = array_map(fn($h) => strtolower(str_replace(' ', '_', trim((string)$h))), $values[0]);
        $rows = [];
        $limit = (int)($request->input('limit', 50));
        for ($i = 1; $i < min(count($values), $limit + 1); $i++) {
            $row = [];
            foreach ($headers as $idx => $h) {
                $row[$h] = $values[$i][$idx] ?? '';
            }
            // Skip empty rows
            if (count(array_filter($row, fn($v) => $v !== '' && $v !== null)) === 0) continue;
            $rows[] = $row;
        }

        return response()->json([
            'success' => true,
            'headers' => $headers,
            'rows' => $rows,
        ]);
    }
}
