<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<link
  href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Dancing+Script:wght@400;700&display=swap"
  rel="stylesheet"
/>
<link 
  href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital@1&display=swap" 
  rel="stylesheet" 
/>
<link
  href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@1,400;1,700&display=swap"
  rel="stylesheet"
/>
    <head>
        <meta charset="utf-8">
        <!-- Desktop & Android -->
        <meta name="theme-color" content="white" />
        <meta name="color-scheme" content="white" />

        <!-- iOS Safari (when saved to Home Screen / PWA) -->
        <meta name="apple-mobile-web-app-capable" content="yes" />

        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js','resources/css/app.css',"resources/js/Pages/{$page['component']}.vue"])
        
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

