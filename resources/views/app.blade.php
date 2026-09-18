<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Registre as horas da bolsa e gere o relatório mensal em PDF.">
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/images/logo_clip.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet">
    <script>
        (function () {
            const stored = localStorage.getItem('clips-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (stored === 'dark' || (stored !== 'light' && prefersDark)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.ts'])
    @inertiaHead
</head>
<body class="h-full">
    @inertia
    <noscript>O Clips precisa de JavaScript para funcionar.</noscript>
</body>
</html>
