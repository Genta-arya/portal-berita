<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AntiScrapeProtection
{
    /**
     * Daftar bot mesin pencari resmi yang diizinkan (Whitelisted Good Crawlers).
     * Bot-bot ini bebas merayapi (crawling) untuk indeks SEO tanpa hambatan.
     */
    protected array $searchEngineBots = [
        'googlebot',
        'google-inspectiontool',
        'mediapartners-google',
        'feedfetcher-google',
        'google-read-aloud',
        'adsbot-google',
        'bingbot',
        'msnbot',
        'yandex',
        'slurp',
        'duckduckbot',
        'baiduspider',
        'facebookexternalhit',
        'meta-externalagent',
        'twitterbot',
        'linkedinbot',
        'whatsapp',
        'telegrambot',
        'pinterestbot',
        'applebot',
    ];

    /**
     * Bad bots & automated headless scrapers / content rippers.
     */
    protected array $knownScrapers = [
        'bytespider',       // ByteDance agresif content scraper
        'claude-web',       // AI rippers non-search
        'amazonbot',
        'ahrefsbot',
        'semrushbot',
        'dotbot',
        'mj12bot',
        'dataforseobot',
        'serpstatbot',
        'zoominfobot',
        'scrapy',
        'curl',
        'python-requests',
        'aiohttp',
        'httpx',
        'guzzlehttp',
        'wget',
        'libwww-perl',
        'winhttp',
        'headlesschrome',
        'phantomjs',
        'puppeteer',
        'playwright',
        'selenium',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = strtolower($request->header('User-Agent', ''));

        // 1. Blokir User-Agent kosong atau mencurigakan (browser nyata dan bot pencari selalu punya User-Agent)
        if (empty(trim($userAgent))) {
            return response('Access Denied: Empty User-Agent', 403);
        }

        // 2. Cek apakah ini Good Bot (Googlebot, Bingbot, dll)
        if ($this->isSearchEngineBot($userAgent)) {
            // Biarkan lewat sepenuhnya untuk indexing SEO 100% lancar
            $response = $next($request);
            $response->headers->set('X-Robots-Tag', 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1');
            return $response;
        }

        // 3. Blokir Known Aggressive Scraper Bots
        if ($this->isKnownScraper($userAgent)) {
            // Blokir akses dengan 403 Forbidden
            return response('Access Denied: Scraping is prohibited.', 403);
        }

        // 4. Rate limiting per IP untuk mencegah high-speed scraping / crawling beruntun
        // Batasi 90 request per menit untuk visitor umum (sangat cukup untuk manusia membaca berita)
        $ip = $request->ip();
        $rateLimitKey = 'antiscrape:' . $ip;

        try {
            $hits = \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 60);
            if ($hits > 90) {
                return response('Too Many Requests: Please slow down.', 429, [
                    'Retry-After' => 60,
                ]);
            }
        } catch (\Throwable $e) {
            // Jika Redis/cache gagal, jangan hentikan eksekusi web
        }

        $response = $next($request);

        // Pasang header keamanan anti-embedding & attribution
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        return $response;
    }

    protected function isSearchEngineBot(string $ua): bool
    {
        foreach ($this->searchEngineBots as $bot) {
            if (str_contains($ua, $bot)) {
                return true;
            }
        }
        return false;
    }

    protected function isKnownScraper(string $ua): bool
    {
        foreach ($this->knownScrapers as $bot) {
            if (str_contains($ua, $bot)) {
                return true;
            }
        }
        return false;
    }
}
