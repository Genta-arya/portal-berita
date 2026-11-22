<?php

namespace App\Services;

class ContentSanitizer
{
    public function clean(string $html, bool $page = false): string
    {
        // DOMDocument's encoding hint is parser metadata, never article content.
        $markers = ['<?xml encoding="utf-8"?>', '<?xml encoding="UTF-8"?>'];
        foreach ($markers as $marker) {
            $html = str_replace([$marker, htmlspecialchars($marker, ENT_NOQUOTES, 'UTF-8'), htmlspecialchars($marker, ENT_QUOTES, 'UTF-8')], '', $html);
        }
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,h2,h3,strong,em,u,s,ul,ol,li,blockquote,table,thead,tbody,tfoot,tr,th[colspan|rowspan|scope],td[colspan|rowspan],a[href|title],img[src|alt|width|height],iframe[src|width|height|title]');
        if ($page) {
            $config->set('HTML.Allowed', 'div[class],span[class],h1[class],h2[class],h3[class],h4[class],p[class],br,strong[class],em,ul[class],ol[class],li,blockquote,a[href|title|class]');
            $config->set('Attr.AllowedClasses', ['kr-about-wrapper','kr-about-hero','kr-about-badge','kr-about-title','kr-about-lead','kr-about-content','kr-about-section','kr-about-grid-2','kr-about-text','kr-about-stats-card','kr-stat-item','kr-stat-num','kr-stat-label','kr-cards-grid','kr-about-card','kr-card-icon-wrap','kr-values-grid','kr-value-box','kr-value-number','kr-about-footer-card','kr-footer-card-inner','kr-about-actions','kr-btn-outline','kr-btn-primary']);
        }
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '~^https://www.youtube-nocookie.com/embed/[A-Za-z0-9_-]{11}(?:\?[A-Za-z0-9_=&%\-]*)?$~D');
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
