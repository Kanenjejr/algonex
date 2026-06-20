<?php
// app/Http/Middleware/EncryptHtmlResponse.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class EncryptHtmlResponse
{
    public function handle($request, Closure $next)
    {
        // Decrypt endpoint (kept for authorized manual fetches; not used by wrapper automatically)
        if ($request->headers->has('X-Decrypt-Token') && $request->query('__decrypt') === '1') {
            if (! $request->headers->has('X-Requested-With') ||
                strtolower($request->header('X-Requested-With')) !== 'xmlhttprequest') {
                return response('Forbidden', 403);
            }

            $referer = $request->headers->get('referer');
            if ($referer) {
                $refererHost = parse_url($referer, PHP_URL_HOST);
                $serverHost = $request->getHost();
                if ($refererHost !== $serverHost) {
                    return response('Forbidden', 403);
                }
            }

            $token = $request->header('X-Decrypt-Token');
            $html = Cache::pull($token);

            if ($html) {
                return response($html, 200)
                    ->header('Content-Type', 'text/html; charset=UTF-8');
            }

            return response('Not found or expired', 404);
        }

        $response = $next($request);

        // Only change top-level GET HTML page responses with status 200
        $status = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 200;
        $contentType = $response->headers->get('Content-Type') ?? '';

        if ($status !== 200 || ! str_contains($contentType, 'text/html')) {
            return $response;
        }

        // Do not wrap AJAX/XHR/JSON requests
        if ($request->ajax() || $request->wantsJson() || $request->headers->has('X-Requested-With')) {
            return $response;
        }

        // ONLY when explicit flag present
        if ($request->query('__encrypt_view') !== '1') {
            return $response;
        }

        $originalHtml = $response->getContent();

        // Generate per-request payload using your existing helper
        $perRequestPassword = bin2hex(random_bytes(16));
        $payload = encryptHtmlWithPassword($originalHtml, $perRequestPassword);

        // Store plaintext for single use under store_id
        $ttl = intval(env('VIEW_ENCRYPTION_CACHE_TTL', 120));
        Cache::put($payload['store_id'], $originalHtml, $ttl);

        $payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

        // Truncate to 15,000 characters for display
        $max = 15000;
        $truncated = mb_substr($payloadJson, 0, $max, '8bit');
        if (mb_strlen($payloadJson, '8bit') > $max) {
            $truncated .= "\n\n... (truncated)";
        }

        // Minimal wrapper: show only the encrypted JSON payload (truncated), white background, readable text.
        $escapedPayloadForPre = htmlspecialchars($truncated, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5);

        $wrapper = <<<HTML
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MBOGO INFO APP+ — System</title>
    <link rel="shortcut icon" href="{{ asset('icon.png') }}" />
  <style>
    /* White page background, readable black text */
    html,body{height:100%;margin:0;background:#ffffff;color:#111;font-family:system-ui,Arial,Helvetica,sans-serif}
    .container{max-width:1100px;margin:28px auto;padding:20px}
    .card{background:#ffffff;border-radius:6px;padding:12px;border:1px solid #e9e9e9;box-shadow:none}
    h1{font-size:18px;margin:0 0 8px 0}
    .note{color:#666;font-size:13px;margin-bottom:12px}
    pre#payload{white-space:pre-wrap;word-break:break-word;background:#ffffff;color:#111;padding:12px;border-radius:4px;border:1px solid #efefef;overflow:auto;max-height:70vh}
    .meta{margin-top:12px;color:#555;font-size:13px}
  </style>
</head>
<body>
  <div class="container">
    <div class="card">
      <pre id="payload" aria-label="encrypted-payload">{$escapedPayloadForPre}</pre>
    </div>
  </div>
</body>
</html>
HTML;

        $response->setContent($wrapper);
        $response->headers->set('Content-Type', 'text/html; charset=UTF-8');

        return $response;
    }
}