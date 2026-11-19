<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>Ruang redaksi</title>@vite(['resources/css/sage.css'])</head>
<body><main style="max-width:1100px;margin:auto;padding:32px"><a href="/">Portal Berita</a><h1>Ruang redaksi</h1><p>Selamat datang, {{ auth()->user()->name }}.</p><p>Peran: {{ auth()->user()->role }}</p><p>Modul artikel sedang disiapkan.</p><form method="post" action="/logout">@csrf<button>Keluar</button></form></main></body></html>
