<?php

namespace App\Services;

class ContentSanitizer
{
    public function clean(string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,h2,h3,strong,em,ul,ol,li,blockquote,a[href|title],img[src|alt|width|height],iframe[src|width|height|title]');
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '~^https://www.youtube-nocookie.com/embed/[A-Za-z0-9_-]{11}$~D');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true]);
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $clean = (new \HTMLPurifier($config))->purify($html);
        $doc = new \DOMDocument;
        @$doc->loadHTML('<?xml encoding="utf-8"?><div>'.$clean.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        foreach (iterator_to_array($doc->getElementsByTagName('img')) as $img) {
            if (! preg_match('~^/media/[0-9]+/(480|1280)$~D', $img->getAttribute('src'))) {
                $img->parentNode->removeChild($img);
            }
        }
        $wrapper = $doc->getElementsByTagName('div')->item(0);

        return $wrapper ? implode('', array_map(fn ($node) => $doc->saveHTML($node), iterator_to_array($wrapper->childNodes))) : '';
    }
}
