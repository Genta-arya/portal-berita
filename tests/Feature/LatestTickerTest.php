<?php
namespace Tests\Feature;
use App\Models\{Article,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LatestTickerTest extends TestCase
{
    use RefreshDatabase;
    public function test_ticker_contains_ten_latest_published_articles(): void
    {
        $user=User::factory()->create();
        for($i=1;$i<=13;$i++){
            $a=new Article(['title'=>'Ticker '.$i,'slug'=>'ticker-'.$i]);$a->author_id=$user->id;$a->status=$i===13?'draft':'published';$a->published_at=$i===13?null:now()->subHour();$a->save();
        }
        $response=$this->get('/')->assertOk();$doc=new \DOMDocument;@$doc->loadHTML($response->getContent());$xpath=new \DOMXPath($doc);
        $links=$xpath->query('//ul[@class="ticker-items"]/li/a');
        $this->assertSame(10,$links->length);$this->assertSame('Ticker 12',$links->item(0)->textContent);$this->assertSame('Ticker 3',$links->item(9)->textContent);
    }
    public function test_editor_youtube_output_survives_sanitization_but_other_iframes_do_not(): void
    {
        $html=app(\App\Services\ContentSanitizer::class)->clean('<iframe src="https://www.youtube-nocookie.com/embed/abcdefghijk?controls=1&amp;rel=0" width="560" height="315"></iframe><iframe src="https://evil.test/embed/abcdefghijk"></iframe>');
        $this->assertStringContainsString('youtube-nocookie.com/embed/abcdefghijk',$html);$this->assertStringNotContainsString('evil.test',$html);
    }
}
