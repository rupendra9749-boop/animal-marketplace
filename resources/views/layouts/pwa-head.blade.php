{{-- Lets Chrome offer "Install app" (no download warnings). Not used inside the AnimalMandi Android app itself. --}}
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}" crossorigin="use-credentials">
<meta name="theme-color" content="#d97706">
<link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">
<script>
    if ('serviceWorker' in navigator && !/; wv\)/.test(navigator.userAgent)) {
        window.addEventListener('load', function () { navigator.serviceWorker.register(@js(asset('sw.js'))).catch(function () {}); });
    }
</script>
