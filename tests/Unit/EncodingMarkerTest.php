<?php
namespace Tests\Unit;

use App\Services\ContentSanitizer;
use Tests\TestCase;

class EncodingMarkerTest extends TestCase
{
    public function test_parser_encoding_markers_do_not_become_article_text(): void
    {
        $sanitizer = new ContentSanitizer;
        foreach (['<?xml encoding="utf-8"?>', '&lt;?xml encoding="utf-8"?&gt;', '&lt;?xml encoding=&quot;utf-8&quot;?&gt;'] as $marker) {
            $clean = $sanitizer->clean($marker.'<p>Berita daerah — café, warga &amp; redaksi.</p>');
            $this->assertStringNotContainsString('xml encoding', $clean);
            $this->assertStringContainsString('Berita daerah — café', $clean);
            $this->assertSame($clean, $sanitizer->clean($clean));
        }
    }
}
