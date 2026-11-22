<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('code', '404') — @yield('title', 'Halaman Tidak Ditemukan') — {{ config('app.name', 'Kapuas Raya') }}</title>
    <link rel="icon" href="/images/logo-kapuasraya.png" type="image/png">
    @vite(['resources/css/sage.css', 'resources/css/editorial.css'])
    <style>
        .kr-error-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: var(--paper, #ffffff);
            color: var(--ink, #0f172a);
            font-family: Arial, Helvetica, sans-serif;
        }
        .kr-error-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--line, #e2e8f0);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .kr-error-brand img {
            height: 40px;
            width: auto;
            object-fit: contain;
        }
        .kr-error-container {
            max-width: 680px;
            margin: auto;
            padding: 48px 24px;
            text-align: center;
        }
        .kr-error-badge {
            display: inline-block;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.5px;
            color: #dc2626;
            background: #fef2f2;
            border: 1px solid #fecaca;
            padding: 4px 14px;
            border-radius: 9999px;
            margin-bottom: 20px;
        }
        .kr-error-code {
            font-size: clamp(80px, 15vw, 130px);
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.05em;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #991b1b 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0 0 16px;
        }
        .kr-error-title {
            font-size: clamp(24px, 4vw, 32px);
            font-weight: 800;
            letter-spacing: -0.02em;
            margin: 0 0 14px;
            color: var(--ink, #0f172a);
        }
        .kr-error-message {
            font-size: 15px;
            line-height: 1.7;
            color: var(--muted, #64748b);
            margin: 0 auto 32px;
            max-width: 520px;
        }
        .kr-error-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .kr-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #ffffff !important;
            text-decoration: none;
            box-shadow: 0 3px 10px rgba(220, 38, 38, 0.35);
            transition: all 0.2s;
        }
        .kr-btn-primary:hover {
            background: linear-gradient(135deg, #f87171, #ef4444);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.45);
        }
        .kr-btn-secondary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            background: var(--wash, #f8fafc);
            color: var(--ink, #1e293b) !important;
            border: 1px solid var(--line, #e2e8f0);
            text-decoration: none;
            transition: all 0.2s;
        }
        .kr-btn-secondary:hover {
            background: #f1f5f9;
            color: #dc2626 !important;
            border-color: #fca5a5;
        }
        .kr-error-footer {
            padding: 20px 24px;
            text-align: center;
            font-size: 13px;
            color: var(--muted, #94a3b8);
            border-top: 1px solid var(--line, #e2e8f0);
        }

        :root[data-theme='dark'] .kr-error-page {
            background: #0b111e;
            color: #f8fafc;
        }
        :root[data-theme='dark'] .kr-error-header,
        :root[data-theme='dark'] .kr-error-footer {
            border-color: #1e293b;
        }
        :root[data-theme='dark'] .kr-error-badge {
            background: rgba(220, 38, 38, 0.15);
            border-color: #ef4444;
            color: #f87171;
        }
        :root[data-theme='dark'] .kr-error-title {
            color: #f8fafc;
        }
        :root[data-theme='dark'] .kr-error-message {
            color: #94a3b8;
        }
        :root[data-theme='dark'] .kr-btn-secondary {
            background: #182234;
            border-color: #2e3a50;
            color: #e2e8f0 !important;
        }
    </style>
</head>
<body class="reader kr-error-page">
    <header class="kr-error-header">
        <a href="/" class="kr-error-brand" aria-label="{{ config('app.name', 'Kapuas Raya') }}">
            <img src="/images/logo-kapuasraya.png" width="180" height="40" alt="{{ config('app.name', 'Kapuas Raya') }}">
        </a>
        <a href="/" class="kr-btn-secondary" style="padding: 6px 14px; font-size: 12px;">Ke Beranda &rarr;</a>
    </header>

    <main class="kr-error-container">
        <span class="kr-error-badge">@yield('badge', 'KESALAHAN NAVIGASI')</span>
        <div class="kr-error-code">@yield('code', '404')</div>
        <h1 class="kr-error-title">@yield('title', 'Halaman Tidak Ditemukan')</h1>
        <p class="kr-error-message">@yield('message', 'Mohon maaf, tautan yang Anda tuju mungkin sudah dipindahkan, dihapus, atau alamat URL yang dimasukkan kurang tepat.')</p>

        <div class="kr-error-actions">
            <a href="/" class="kr-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Kembali ke Beranda
            </a>
            <a href="/terbaru" class="kr-btn-secondary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Indeks Berita Terbaru
            </a>
        </div>
    </main>

    <footer class="kr-error-footer">
        <span>&copy; {{ date('Y') }} {{ config('app.name', 'Kapuas Raya') }}. Seluruh hak cipta dilindungi.</span>
    </footer>
</body>
</html>
