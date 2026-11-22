<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Masuk Redaksi — Kapuas Raya</title>
    <link rel="icon" href="/images/icon-kapuasraya.png">
    @vite(['resources/css/login.css'])
</head>
<body>
    <main class="login-shell">
        <aside class="login-editorial" aria-label="Kapuas Raya">
            <a class="editorial-brand" href="/" aria-label="Beranda Kapuas Raya"><img src="/images/logo-kapuasraya.png" alt="Kapuas Raya" width="200" height="50"></a>
            <div class="editorial-message">
                <span class="eyebrow"><span></span> RUANG REDAKSI</span>
                <h1>Kabar bermakna.<br>Dari tangan<br><em>redaksi.</em></h1>
                <p>Ruang untuk merangkai cerita, mengabarkan fakta, dan menghadirkan perspektif dari Kalimantan Barat.</p>
                <div class="editorial-rule"><span>LOKAL. RELEVAN. TERPERCAYA.</span><span>KR / REDAKSI</span></div>
            </div>
            <span class="editorial-caption">KAPUAS RAYA &nbsp; / &nbsp; PORTAL BERITA</span>
        </aside>
        <section class="login-panel" aria-labelledby="login-title">
            <a class="back-link" href="/"><span aria-hidden="true">&larr;</span> Kembali ke beranda</a>
            <div class="login-content">
                <span class="form-kicker">SELAMAT DATANG KEMBALI</span>
                <h2 id="login-title">Masuk ke redaksi</h2>
                <p class="form-description">Lanjutkan cerita dan kelola pemberitaan Anda.</p>
                @if($errors->any())
                    <div class="login-error" role="alert" id="login-error">{{ $errors->first() }}</div>
                @endif
                <form method="post" action="/login">
                    @csrf
                    <div class="field">
                        <label for="email">Alamat email</label>
                        <input id="email" name="email" type="email" autocomplete="username" placeholder="nama@contoh.com" value="{{ old('email') }}" required @if($errors->has('email')) aria-invalid="true" aria-describedby="login-error" @endif>
                    </div>
                    <div class="field">
                        <label for="password">Kata sandi</label>
                        <div class="password-field">
                            <input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required @if($errors->has('password')) aria-invalid="true" aria-describedby="login-error" @endif>
                            <button class="password-toggle" type="button" aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false" hidden>
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </button>
                        </div>
                    </div>
                    <button class="login-submit" type="submit">Masuk ke ruang redaksi <span aria-hidden="true">&rarr;</span></button>
                </form>
                <p class="access-note"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg> Akses khusus tim redaksi Kapuas Raya.</p>
                <p class="help-note">Kesulitan masuk? Hubungi administrator redaksi.</p>
            </div>
            <footer>&copy; {{ date('Y') }} Kapuas Raya. Semua hak dilindungi.</footer>
        </section>
    </main>
    <script>
        const toggle = document.querySelector('.password-toggle');
        const password = document.getElementById('password');
        toggle.hidden = false;
        toggle.addEventListener('click', () => {
            const visible = password.type === 'password';
            password.type = visible ? 'text' : 'password';
            toggle.setAttribute('aria-pressed', String(visible));
            toggle.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
        });
    </script>
</body>
</html>
