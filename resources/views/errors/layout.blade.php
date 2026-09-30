{{-- A small, self-contained error screen (no build files needed, so it still works when something is badly wrong). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} &middot; {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:500,700,800{{ app()->getLocale() === 'hi' ? '|noto-sans-devanagari:500,700,800' : '' }}&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; background: #fafaf9; color: #1c1917; font-family: 'Plus Jakarta Sans', 'Noto Sans Devanagari', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif; }
        .box { max-width: 420px; text-align: center; }
        .logo { width: 56px; height: 56px; margin: 0 auto 20px; border-radius: 16px; background: #d97706; color: #fff; font-size: 28px; line-height: 56px; }
        .code { font-size: 14px; font-weight: 700; letter-spacing: .08em; color: #b45309; }
        html[lang='hi'] .code { letter-spacing: 0; }
        h1 { margin: 6px 0 10px; font-size: 26px; font-weight: 800; line-height: 1.45; }
        p { margin: 0 0 24px; color: #57534e; line-height: 1.7; }
        a.btn { display: inline-block; padding: 12px 22px; border-radius: 12px; background: #d97706; color: #fff; font-weight: 700; text-decoration: none; }
    </style>
</head>
<body>
    <div class="box">
        <div class="logo">&#128062;</div>
        <div class="code">{{ $code }}</div>
        <h1>{{ $title }}</h1>
        <p>{{ $message }}</p>
        <a class="btn" href="{{ url('/home') }}">{{ __('Back to home') }}</a>
    </div>
</body>
</html>
