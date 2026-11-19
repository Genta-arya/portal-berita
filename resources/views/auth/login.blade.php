<!doctype html>
<html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex"><title>Login redaksi — Portal Berita</title>@vite(['resources/css/sage.css'])</head>
<body><main style="max-width:420px;margin:10vh auto;padding:24px"><a href="/">Portal Berita</a><h1>Masuk ke ruang redaksi</h1>
@if($errors->any())<p role="alert">{{ $errors->first() }}</p>@endif
<form method="post" action="/login">@csrf
<p><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" value="{{ old('email') }}" required></p>
<p><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></p>
<button type="submit">Masuk</button></form></main></body></html>
