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
