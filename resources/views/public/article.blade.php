@extends('layouts.public')
@section('title',$article->seo_title ?: $article->title)
@section('description',($article->seo_description ?: $article->summary) ?? '')
@section('canonical',$article->url)
@section('ogtype','article')
@push('head')
@if($article->hero_media_id)<meta property="og:image" content="{{ url('/media/'.$article->hero_media_id.'/1280') }}">@endif
@if(!isset($preview))<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'NewsArticle','headline'=>$article->title,'description'=>$article->summary,'datePublished'=>$article->published_at?->toIso8601String(),'dateModified'=>$article->updated_at?->toIso8601String(),'author'=>['@type'=>'Person','name'=>$article->guest_author ?: $article->author->name],'mainEntityOfPage'=>$article->url,'image'=>[url('/media/'.$article->hero_media_id.'/1280')]],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) !!}</script>@endif
@endpush

@section('content')
<div class="reader-breadcrumb">
    <a href="/">Beranda</a>
    @if($article->category)
        <span aria-hidden="true">/</span>
        <a href="/kanal/{{ $article->category->slug }}">{{ $article->category->name }}</a>
    @endif
</div>

{{-- Layout 2 Kolom Seimbang dengan Navbar: Konten Artikel (Kiri) & Sidebar Trending/Lainnya (Kanan) --}}
<div class="article-layout-grid">
    {{-- KOLOM KIRI: Detail Artikel & Rekomendasi Baca Juga --}}
    <div class="article-main-col">
        <article class="reading" @if(!isset($preview)) data-article-id="{{ \App\Models\Article::hashId($article->id) }}" @endif>
            @isset($preview)
                <p class="notice">Preview privat · {{ $article->status }}</p>
            @endisset
            <div class="reading-badge-wrap">
                <a class="eyebrow kr-category-pill" href="/kanal/{{ $article->category?->slug }}">
                    <span class="kr-pill-flame" aria-hidden="true">
                        <i class="ember ember-1"></i>
                        <i class="ember ember-2"></i>
                        <i class="ember ember-3"></i>
                    </span>
                    <span class="kr-pill-hash" aria-hidden="true">#</span>
                    <span>{{ $article->category?->name }}</span>
                </a>
                @if($article->sponsored)
                    <p class="badge">Konten sponsor</p>
                @endif
            </div>
            <h1>{{ $article->title }}</h1>
            {{-- Redesigned Author Byline Card --}}
            <div class="kr-article-byline" aria-label="Informasi Penulis dan Waktu Terbit">
                <div class="kr-byline-avatar">
                    @if($article->author?->avatar_path)
                        <img src="/author/{{ $article->author_id }}/avatar" alt="{{ $article->author->name }}" width="44" height="44" loading="lazy">
                    @else
                        <span class="kr-avatar-fallback" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                    @endif
                </div>
                <div class="kr-byline-meta">
                    <div class="kr-byline-author-row">
                        <span class="kr-byline-prefix">Post oleh:</span>
                        <a href="/penulis/{{ $article->author->slug }}" class="kr-byline-name">{{ $article->guest_author ?: $article->author->name }}</a>
                        @if($article->editor_id)
                            <span class="kr-byline-editor">· Editor: {{ \App\Models\User::find($article->editor_id)?->name }}</span>
                        @endif
                    </div>
                    <div class="kr-byline-date-row">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <time datetime="{{ $article->published_at?->toIso8601String() }}">
                            {{ $article->published_at?->timezone('Asia/Jakarta')->translatedFormat('l, d F Y · H:i') }} WIB
                        </time>
                    </div>
                </div>
            </div>
            {{-- Text-To-Speech (Bacakan Berita) Player Bar --}}
            <div class="kr-tts-player" id="krTtsPlayer">
                <div class="kr-tts-info">
                    <div class="kr-tts-icon-wrap" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                            <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                        </svg>
                    </div>
                    <div class="kr-tts-meta">
                        <span class="kr-tts-label">Dengarkan Berita Ini</span>
                        <span class="kr-tts-status" id="krTtsStatus"></span>
                    </div>
                </div>

                <div class="kr-tts-controls">
                    <button type="button" class="kr-tts-btn kr-tts-btn--play" id="krTtsPlayBtn" aria-label="Putar bacaan berita" title="Putar">
                        <svg class="kr-tts-play-icon" viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span id="krTtsPlayText">Putar</span>
                    </button>
                    <button type="button" class="kr-tts-btn kr-tts-btn--pause" id="krTtsPauseBtn" aria-label="Jeda bacaan" title="Jeda" style="display: none;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                        <span>Jeda</span>
                    </button>
                    <button type="button" class="kr-tts-btn kr-tts-btn--stop" id="krTtsStopBtn" aria-label="Hentikan bacaan" title="Berhenti" style="display: none;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                            <rect x="5" y="5" width="14" height="14" rx="2"></rect>
                        </svg>
                        <span>Berhenti</span>
                    </button>
                </div>
            </div>

            @if($article->hero_media_id)
                <figure class="article-figure-wrap">
                    <div class="article-image-container" id="krHeroImageContainer">
                        <img class="article-image" id="krHeroArticleImage" src="/media/{{ $article->hero_media_id }}/1280" width="1280" height="800" alt="{{ $article->hero_alt }}" fetchpriority="high">
                        <span class="thumbnail-watermark article-watermark" aria-hidden="true">
                            <img src="/images/logo-kapuasraya.png" width="160" height="34" alt="KAPUAS RAYA">
                        </span>
                        <button type="button" class="kr-image-zoom-trigger" id="krImageZoomTrigger" aria-label="Lihat gambar ukuran penuh" title="Klik untuk memperbesar gambar">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="11" y1="8" x2="11" y2="14"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                            <span>Lihat Gambar</span>
                        </button>
                    </div>
                    <figcaption>{{ $article->hero_caption }} · {{ $article->hero_credit }}</figcaption>
                </figure>
            @endif

            @php
                $renderedBody = $article->body;
                if ($related->isNotEmpty()) {
                    // Split content by closing paragraph tags
                    $parts = preg_split('/(<\/p>)/i', $renderedBody, -1, PREG_SPLIT_DELIM_CAPTURE);
                    $paragraphs = [];
                    $totalParts = count($parts);
                    for ($i = 0; $i < $totalParts - 1; $i += 2) {
                        $paragraphs[] = $parts[$i] . $parts[$i + 1];
                    }
                    if ($totalParts % 2 === 1 && trim($parts[$totalParts - 1]) !== '') {
                        $paragraphs[] = $parts[$totalParts - 1];
                    }

                    $totalP = count($paragraphs);
                    $inlineItems = $related->take(3)->values();
                    $numItems = $inlineItems->count();

                    if ($totalP >= 2 && $numItems > 0) {
                        // Distribute up to 3 cards evenly inside article body
                        if ($numItems === 1) {
                            $positions = [max(1, (int) floor($totalP / 2))];
                        } elseif ($numItems === 2) {
                            $positions = [max(1, (int) floor($totalP / 3)), max(2, (int) floor((2 * $totalP) / 3))];
                        } else {
                            if ($totalP >= 6) {
                                $positions = [
                                    max(1, (int) floor($totalP / 4)),
                                    max(2, (int) floor((2 * $totalP) / 4)),
                                    max(3, (int) floor((3 * $totalP) / 4))
                                ];
                            } elseif ($totalP >= 4) {
                                $positions = [1, 2, 3];
                            } elseif ($totalP === 3) {
                                $positions = [1, 2];
                            } else {
                                $positions = [1];
                            }
                        }

                        $insertMap = [];
                        foreach ($positions as $idx => $pos) {
                            if (isset($inlineItems[$idx])) {
                                $insertMap[$pos][] = $inlineItems[$idx];
                            }
                        }

                        $newBody = '';
                        foreach ($paragraphs as $pIndex => $pContent) {
                            $newBody .= $pContent;
                            $curNumber = $pIndex + 1;
                            if (isset($insertMap[$curNumber])) {
                                foreach ($insertMap[$curNumber] as $relItem) {
                                    $newBody .= view('public.partials.inline-related', ['relItem' => $relItem])->render();
                                }
                            }
                        }
                        $renderedBody = $newBody;
                    }
                }
            @endphp

            <div class="prose" id="krArticleProse">{!! $renderedBody !!}</div>

            @if($article->tags->isNotEmpty())
            <div class="tags kr-article-tags">
                <span class="kr-tags-heading">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                        <line x1="7" y1="7" x2="7.01" y2="7"></line>
                    </svg>
                    Topik Terkait:
                </span>
                @foreach($article->tags as $tag)
                    <a href="/tag/{{ $tag->slug }}" class="kr-tag-pill">#{{ $tag->name }}</a>
                @endforeach
            </div>
            @endif

            <div class="share">
                <strong class="share-title">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="18" cy="5" r="3"></circle>
                        <circle cx="6" cy="12" r="3"></circle>
                        <circle cx="18" cy="19" r="3"></circle>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                    </svg>
                    Bagikan:
                </strong>
                <a href="https://wa.me/?text={{ rawurlencode($article->title.' '.$article->url) }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn--wa" aria-label="Bagikan ke WhatsApp">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                        <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2zm0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.167 8.167 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.02 4.54-3.68 8.23-8.22 8.23zm4.52-6.17c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.22.25-.86.84-.86 2.05 0 1.21.88 2.38 1.01 2.55.12.17 1.74 2.65 4.21 3.72.59.25 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.1-.23-.17-.48-.29z"/>
                    </svg>
                    <span>WhatsApp</span>
                </a>
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($article->url) }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn--fb" aria-label="Bagikan ke Facebook">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" aria-hidden="true">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                    <span>Facebook</span>
                </a>
                <a href="https://twitter.com/intent/tweet?url={{ rawurlencode($article->url) }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn--x" aria-label="Bagikan ke X">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                    </svg>
                    <span>X</span>
                </a>
                <button type="button" data-copy="{{ $article->url }}" class="share-btn share-btn--copy" aria-label="Salin tautan artikel">
                    <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    <span>Salin Tautan</span>
                </button>
            </div>

            @include('public.banner', ['slot' => 'article_bottom'])
        </article>

        @if($related->isNotEmpty())
            <section class="related-stories" aria-labelledby="related-heading">
                <div class="section-bar"><h2 id="related-heading">Baca juga</h2></div>
                <div class="channel-grid">
                    @foreach($related as $item)
                        @include('public.card')
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- KOLOM KANAN: Sidebar Sticky Berita Trending & Berita Menarik Lainnya --}}
    <aside class="listing-aside article-aside">
        {{-- WIDGET 1: Berita Trending --}}
        @if(isset($trendingArticles) && $trendingArticles->isNotEmpty())
            <div class="kr-sidebar-card kr-trending-widget">
                <div class="kr-sidebar-header">
                    <div class="kr-sidebar-title-wrap">
                        <span class="kr-trending-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M23 6l-9.5 9.5-5-5L1 18"></path>
                                <path d="M17 6h6v6"></path>
                            </svg>
                        </span>
                        <h3>Sedang Trending</h3>
                    </div>
                    <span class="kr-badge-pulse">HOT</span>
                </div>
                <ol class="kr-ranked-list">
                    @foreach($trendingArticles as $item)
                        <li class="kr-ranked-item">
                            <span class="kr-rank-num kr-rank-num--{{ $loop->iteration <= 3 ? 'top' : 'normal' }}">{{ $loop->iteration }}</span>
                            <div class="kr-ranked-body">
                                @if($item->category)
                                    <a class="kr-ranked-category" href="/kanal/{{ $item->category->slug }}">{{ $item->category->name }}</a>
                                @endif
                                <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->diffForHumans() }}</time>
                            </div>
                            @if($item->hero_media_id)
                                <a class="kr-ranked-thumb" href="{{ $item->url }}" aria-hidden="true" tabindex="-1">
                                    <img src="/media/{{ $item->hero_media_id }}/480" alt="" width="68" height="54" loading="lazy">
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif

        {{-- WIDGET BANNER IKLAN SIDEBAR --}}
        @include('public.banner', ['slot' => 'sidebar'])

        {{-- WIDGET 2: Berita Pilihan Lainnya --}}
        @if(isset($otherArticles) && $otherArticles->isNotEmpty())
            <div class="kr-sidebar-card kr-other-stories-widget">
                <div class="kr-sidebar-header">
                    <div class="kr-sidebar-title-wrap">
                        <span class="kr-other-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="12 6 12 12 16 14"></polygon>
                            </svg>
                        </span>
                        <h3>Kabar Menarik Lainnya</h3>
                    </div>
                </div>
                <div class="kr-other-stories-list">
                    @foreach($otherArticles as $item)
                        <article class="kr-other-card">
                            @if($item->hero_media_id)
                                <a class="kr-other-thumb" href="{{ $item->url }}">
                                    <img src="/media/{{ $item->hero_media_id }}/480" width="120" height="80" alt="{{ $item->hero_alt }}" loading="lazy">
                                </a>
                            @endif
                            <div class="kr-other-content">
                                @if($item->category)
                                    <a class="kr-other-cat" href="/kanal/{{ $item->category->slug }}">{{ $item->category->name }}</a>
                                @endif
                                <h4><a href="{{ $item->url }}">{{ $item->title }}</a></h4>
                                <time datetime="{{ $item->published_at?->toIso8601String() }}">{{ $item->published_at?->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}</time>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    </aside>
</div>

{{-- Fullscreen Image Lightbox Modal with Zoom In/Out & Pan --}}
<div class="kr-image-lightbox" id="krImageLightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Penampil Gambar Ukuran Penuh">
    <div class="kr-lightbox-backdrop" id="krLightboxBackdrop"></div>
    
    <div class="kr-lightbox-toolbar">
        <div class="kr-lightbox-caption-text" id="krLightboxCaption">
            {{ $article->title }}
        </div>
        <div class="kr-lightbox-controls">
            <button type="button" class="kr-lightbox-btn" id="krZoomOutBtn" aria-label="Perkecil gambar" title="Perkecil (Zoom Out)">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
            </button>
            <span class="kr-zoom-level-badge" id="krZoomLevelBadge">100%</span>
            <button type="button" class="kr-lightbox-btn" id="krZoomInBtn" aria-label="Perbesar gambar" title="Perbesar (Zoom In)">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    <line x1="11" y1="8" x2="11" y2="14"></line>
                    <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
            </button>
            <button type="button" class="kr-lightbox-btn" id="krZoomResetBtn" aria-label="Reset zoom" title="Ukuran Semula (Reset)">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                    <path d="M3 3v5h5"></path>
                </svg>
            </button>
            <button type="button" class="kr-lightbox-btn kr-lightbox-btn--close" id="krLightboxCloseBtn" aria-label="Tutup penampil gambar" title="Tutup (Esc)">
                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>

    <div class="kr-lightbox-stage" id="krLightboxStage">
        <div class="kr-lightbox-img-wrapper" id="krLightboxImgWrapper">
            <img src="" alt="" id="krLightboxImage" class="kr-lightbox-img" draggable="false">
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const lightbox = document.getElementById('krImageLightbox');
    const lightboxImg = document.getElementById('krLightboxImage');
    const lightboxImgWrapper = document.getElementById('krLightboxImgWrapper');
    const lightboxCaption = document.getElementById('krLightboxCaption');
    const backdrop = document.getElementById('krLightboxBackdrop');
    const closeBtn = document.getElementById('krLightboxCloseBtn');
    const zoomInBtn = document.getElementById('krZoomInBtn');
    const zoomOutBtn = document.getElementById('krZoomOutBtn');
    const zoomResetBtn = document.getElementById('krZoomResetBtn');
    const zoomBadge = document.getElementById('krZoomLevelBadge');
    const stage = document.getElementById('krLightboxStage');

    if (!lightbox || !lightboxImg) return;

    let currentScale = 1;
    let translateX = 0;
    let translateY = 0;
    let isDragging = false;
    let startX = 0;
    let startY = 0;

    function updateTransform() {
        if (!lightboxImgWrapper) return;
        lightboxImgWrapper.style.transform = `translate(${translateX}px, ${translateY}px) scale(${currentScale})`;
        if (zoomBadge) zoomBadge.textContent = `${Math.round(currentScale * 100)}%`;
        if (currentScale > 1) {
            stage.classList.add('is-zoomed');
        } else {
            stage.classList.remove('is-zoomed');
            translateX = 0;
            translateY = 0;
        }
    }

    function openLightbox(src, alt, caption) {
        lightboxImg.src = src;
        lightboxImg.alt = alt || '';
        if (lightboxCaption && caption) {
            lightboxCaption.textContent = caption;
        }
        currentScale = 1;
        translateX = 0;
        translateY = 0;
        updateTransform();
        lightbox.classList.add('is-open');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('kr-modal-opened');
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('kr-modal-opened');
        currentScale = 1;
        translateX = 0;
        translateY = 0;
        updateTransform();
    }

    // Pasang click listener pada Hero Image & tombol "Lihat Gambar"
    const heroImgContainer = document.getElementById('krHeroImageContainer');
    const heroImg = document.getElementById('krHeroArticleImage');
    const zoomTrigger = document.getElementById('krImageZoomTrigger');
    const heroFigcaption = document.querySelector('.article-figure-wrap figcaption');

    if (heroImg && heroImgContainer) {
        const heroCaptionText = heroFigcaption ? heroFigcaption.innerText : document.title;

        if (zoomTrigger) {
            zoomTrigger.addEventListener('click', function(e) {
                e.stopPropagation();
                openLightbox(heroImg.src, heroImg.alt, heroCaptionText);
            });
        }

        heroImgContainer.addEventListener('click', function() {
            openLightbox(heroImg.src, heroImg.alt, heroCaptionText);
        });
    }

    // Juga pasang click listener ke gambar di dalam body artikel (.prose img), kecualikan inline related
    const proseImages = document.querySelectorAll('.prose img:not(.kr-inline-related-thumb img)');
    proseImages.forEach(function(img) {
        img.style.cursor = 'zoom-in';
        img.title = 'Klik untuk memperbesar gambar';
        img.addEventListener('click', function() {
            openLightbox(this.src, this.alt, this.alt || document.title);
        });
    });

    if (closeBtn) closeBtn.addEventListener('click', closeLightbox);
    if (backdrop) backdrop.addEventListener('click', closeLightbox);

    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (currentScale < 3.5) {
                currentScale = Math.min(3.5, currentScale + 0.35);
                updateTransform();
            }
        });
    }

    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (currentScale > 0.6) {
                currentScale = Math.max(0.6, currentScale - 0.35);
                updateTransform();
            }
        });
    }

    if (zoomResetBtn) {
        zoomResetBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            currentScale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        });
    }

    // Wheel Zoom
    if (stage) {
        stage.addEventListener('wheel', function(e) {
            e.preventDefault();
            const delta = e.deltaY < 0 ? 0.2 : -0.2;
            const newScale = Math.min(3.5, Math.max(0.6, currentScale + delta));
            if (newScale !== currentScale) {
                currentScale = newScale;
                updateTransform();
            }
        }, { passive: false });

        // Drag to Pan saat ter-zoom
        stage.addEventListener('mousedown', function(e) {
            if (currentScale <= 1) return;
            isDragging = true;
            startX = e.clientX - translateX;
            startY = e.clientY - translateY;
            stage.classList.add('is-dragging');
        });

        window.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            e.preventDefault();
            translateX = e.clientX - startX;
            translateY = e.clientY - startY;
            updateTransform();
        });

        window.addEventListener('mouseup', function() {
            if (isDragging) {
                isDragging = false;
                stage.classList.remove('is-dragging');
            }
        });
    }

    // Keyboard ESC to close, +/- to zoom
    window.addEventListener('keydown', function(e) {
        if (!lightbox.classList.contains('is-open')) return;
        if (e.key === 'Escape') {
            closeLightbox();
        } else if (e.key === '+' || e.key === '=') {
            if (currentScale < 3.5) {
                currentScale = Math.min(3.5, currentScale + 0.35);
                updateTransform();
            }
        } else if (e.key === '-' || e.key === '_') {
            if (currentScale > 0.6) {
                currentScale = Math.max(0.6, currentScale - 0.35);
                updateTransform();
            }
        } else if (e.key === '0') {
            currentScale = 1;
            translateX = 0;
            translateY = 0;
            updateTransform();
        }
    });
    // =========================================================================
    // Text-To-Speech (Bacakan Berita) dengan Highlight Teks Kalimat Akurat
    // =========================================================================
    const ttsPlayer = document.getElementById('krTtsPlayer');
    const ttsPlayBtn = document.getElementById('krTtsPlayBtn');
    const ttsPauseBtn = document.getElementById('krTtsPauseBtn');
    const ttsStopBtn = document.getElementById('krTtsStopBtn');
    const ttsStatus = document.getElementById('krTtsStatus');
    const ttsPlayText = document.getElementById('krTtsPlayText');
    const articleProse = document.getElementById('krArticleProse');

    if ('speechSynthesis' in window && ttsPlayer && articleProse) {
        let sentenceSpans = [];
        let currentIndex = 0;
        let isSpeaking = false;
        let isPaused = false;
        let currentUtterance = null;
        let indonesianVoice = null;

        // Siapkan pembagian kalimat di dalam paragraf artikel untuk highlight presisi
        function prepareSentenceSpans() {
            if (sentenceSpans.length > 0) return;

            const paragraphs = articleProse.querySelectorAll('p:not(.kr-inline-related-p), blockquote, li');
            let spanId = 0;

            paragraphs.forEach(function(p) {
                // Jangan sentuh paragraf yang hanya berisi script, iframe, atau kosong
                const text = p.innerText.trim();
                if (!text || p.querySelector('iframe, script, img')) return;

                // Pisahkan kalimat berdasarkan tanda baca (. ! ?)
                const rawSentences = text.match(/[^.!?]+[.!?]+(\s+|$)|[^.!?]+$/g);
                if (!rawSentences || rawSentences.length === 0) return;

                let html = '';
                rawSentences.forEach(function(s) {
                    const cleanS = s.trim();
                    if (cleanS) {
                        html += `<span class="kr-tts-span" data-tts-idx="${spanId}">${cleanS} </span>`;
                        spanId++;
                    }
                });
                if (html) {
                    p.innerHTML = html;
                }
            });

            sentenceSpans = Array.from(articleProse.querySelectorAll('.kr-tts-span'));
        }

        function loadVoices() {
            const voices = window.speechSynthesis.getVoices();
            indonesianVoice = voices.find(v => v.lang.startsWith('id') || v.lang.includes('ID')) || null;
        }

        loadVoices();
        if (window.speechSynthesis.onvoiceschanged !== undefined) {
            window.speechSynthesis.onvoiceschanged = loadVoices;
        }

        function highlightSentence(index) {
            sentenceSpans.forEach(s => s.classList.remove('is-reading-highlight'));
            if (index >= 0 && index < sentenceSpans.length) {
                const target = sentenceSpans[index];
                target.classList.add('is-reading-highlight');

                // Smooth scroll jika kalimat berada di luar viewport pembaca
                const rect = target.getBoundingClientRect();
                if (rect.top < 150 || rect.bottom > window.innerHeight - 100) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        }

        function playSentence(index) {
            if (index >= sentenceSpans.length) {
                stopSpeech();
                if (ttsStatus) ttsStatus.textContent = 'Selesai dibacakan';
                return;
            }

            currentIndex = index;
            highlightSentence(index);

            const sentenceText = sentenceSpans[index].innerText.trim();
            currentUtterance = new SpeechSynthesisUtterance(sentenceText);
            currentUtterance.lang = 'id-ID';
            currentUtterance.rate = 1.0;
            currentUtterance.pitch = 1.0;

            if (indonesianVoice) {
                currentUtterance.voice = indonesianVoice;
            }

            currentUtterance.onend = function() {
                if (isSpeaking && !isPaused) {
                    playSentence(currentIndex + 1);
                }
            };

            currentUtterance.onerror = function() {
                if (isSpeaking && !isPaused) {
                    playSentence(currentIndex + 1);
                }
            };

            window.speechSynthesis.speak(currentUtterance);
        }

        function startSpeech() {
            prepareSentenceSpans();
            if (sentenceSpans.length === 0) return;

            window.speechSynthesis.cancel();
            isSpeaking = true;
            isPaused = false;

            ttsPlayer.classList.add('is-active');
            if (ttsStatus) ttsStatus.textContent = 'Sedang membaca...';
            if (ttsPlayBtn) ttsPlayBtn.style.display = 'none';
            if (ttsPauseBtn) ttsPauseBtn.style.display = 'inline-flex';
            if (ttsStopBtn) ttsStopBtn.style.display = 'inline-flex';

            playSentence(currentIndex);
        }

        function pauseSpeech() {
            if (!isSpeaking || isPaused) return;

            window.speechSynthesis.cancel();
            isPaused = true;

            if (ttsStatus) ttsStatus.textContent = 'Dijeda';
            if (ttsPlayBtn) {
                ttsPlayBtn.style.display = 'inline-flex';
                if (ttsPlayText) ttsPlayText.textContent = 'Lanjutkan';
            }
            if (ttsPauseBtn) ttsPauseBtn.style.display = 'none';
        }

        function resumeSpeech() {
            if (!isSpeaking || !isPaused) return;

            isPaused = false;
            if (ttsStatus) ttsStatus.textContent = 'Sedang membaca...';
            if (ttsPlayBtn) ttsPlayBtn.style.display = 'none';
            if (ttsPauseBtn) ttsPauseBtn.style.display = 'inline-flex';

            playSentence(currentIndex);
        }

        function stopSpeech() {
            window.speechSynthesis.cancel();
            isSpeaking = false;
            isPaused = false;
            currentIndex = 0;

            highlightSentence(-1);

            ttsPlayer.classList.remove('is-active');
            if (ttsStatus) ttsStatus.textContent = '';
            if (ttsPlayBtn) {
                ttsPlayBtn.style.display = 'inline-flex';
                if (ttsPlayText) ttsPlayText.textContent = 'Putar';
            }
            if (ttsPauseBtn) ttsPauseBtn.style.display = 'none';
            if (ttsStopBtn) ttsStopBtn.style.display = 'none';
        }

        if (ttsPlayBtn) {
            ttsPlayBtn.addEventListener('click', function() {
                if (isPaused) {
                    resumeSpeech();
                } else {
                    startSpeech();
                }
            });
        }

        if (ttsPauseBtn) {
            ttsPauseBtn.addEventListener('click', pauseSpeech);
        }

        if (ttsStopBtn) {
            ttsStopBtn.addEventListener('click', stopSpeech);
        }

        window.addEventListener('beforeunload', function() {
            window.speechSynthesis.cancel();
        });
    } else if (ttsPlayer) {
        ttsPlayer.style.display = 'none';
    }
});
</script>
@endsection
