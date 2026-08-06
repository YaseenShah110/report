<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      x-data="{
        dark: localStorage.getItem('theme') === 'dark' ||
              (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
      }"
      :class="{ 'dark': dark }"
      class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    {{-- Dynamic title via Inertia --}}
    <title inertia>{{ config('app.name', 'ReportCraft') }}</title>

    {{-- ── Preconnect for fonts ────────────────────────────────────── --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />

    {{-- ── Google Fonts ────────────────────────────────────────────── --}}
    {{-- Load all font families used by the editor --}}
    <link 
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..900;1,9..40,100..900&family=Inter:wght@300;400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=Poppins:wght@400;500;600;700;800&family=Figtree:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&family=Nunito:wght@400;600;700;800&family=Outfit:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Merriweather:ital,wght@0,400;0,700;1,400&family=Lora:ital,wght@0,400;0,700;1,400&family=Fira+Code:wght@400;500&display=swap"
        rel="stylesheet"
    />

    {{-- ── Font Awesome 6 (icons) ──────────────────────────────────── --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

    {{-- ── Ziggy routes (enables route() helper in Vue) ────────────── --}}
    @routes

    {{-- ── Vite assets (compiled JS + CSS) ─────────────────────────── --}}
    @viteReactRefresh
    @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])

    {{-- ── Inertia head data (og tags, title overrides etc.) ──────── --}}
    @inertiaHead

    {{-- ── Global CSS ───────────────────────────────────────────────── --}}
    <style>
        /* ── Dark mode transitions ────────────────────────────────────── */
        *, *::before, *::after {
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        /* ── Custom Scrollbar ────────────────────────────────────────── */
        ::-webkit-scrollbar { 
            width: 6px; 
            height: 6px; 
        }
        ::-webkit-scrollbar-track { 
            background: transparent; 
        }
        ::-webkit-scrollbar-thumb { 
            background: #cbd5e1; 
            border-radius: 99px; 
        }
        ::-webkit-scrollbar-thumb:hover { 
            background: #94a3b8; 
        }
        .dark ::-webkit-scrollbar-thumb { 
            background: #334155; 
        }
        .dark ::-webkit-scrollbar-thumb:hover { 
            background: #475569; 
        }

        /* ── Base reset ── */
        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        /* ── App shell (non-editor pages use standard Tailwind) ── */
        #app {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* ── Global print CSS ────────────────────────────────────────── */
        {{-- Ensures only the report content prints, not the app chrome --}}
        @media print {
            /* Hide all app shell elements when printing from the preview page */
            .pv-toolbar,
            nav,
            header,
            aside,
            footer:not(.pv-footer),
            .editor-shell {
                display: none !important;
            }

            body {
                background: #fff !important;
                margin: 0;
                padding: 0;
            }

            /* Page break between report pages */
            .pv-page {
                page-break-after: always;
                break-after: page;
            }

            .pv-page:last-child {
                page-break-after: auto;
                break-after: auto;
            }

            @page {
                margin: 0;
                padding: 0;
            }
        }

        /* ── Dark mode body styles ────────────────────────────────────── */
        body {
            background-color: #f8fafc;
            color: #0f172a;
        }

        .dark body {
            background-color: #0f172a;
            color: #f1f5f9;
        }

        /* ── Additional dark mode utilities ──────────────────────────── */
        .dark .bg-white {
            background-color: #1e293b !important;
        }
        .dark .bg-slate-50 {
            background-color: #0f172a !important;
        }
        .dark .bg-slate-100 {
            background-color: #1e293b !important;
        }
        .dark .text-slate-600 {
            color: #94a3b8 !important;
        }
        .dark .text-slate-700 {
            color: #cbd5e1 !important;
        }
        .dark .border-slate-200 {
            border-color: #334155 !important;
        }
        .dark .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.4), 0 4px 6px -2px rgba(0, 0, 0, 0.3) !important;
        }
        .dark .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.3) !important;
        }
        .dark .shadow-sm {
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.4) !important;
        }
        .dark .hover\:bg-slate-50:hover {
            background-color: #1e293b !important;
        }
        .dark .hover\:bg-slate-100:hover {
            background-color: #334155 !important;
        }
        .dark .divide-slate-200 > * + * {
            border-color: #334155 !important;
        }
        .dark .ring-slate-300 {
            --tw-ring-color: #334155 !important;
        }
        .dark .focus\:ring-slate-300:focus {
            --tw-ring-color: #334155 !important;
        }
        .dark .placeholder-slate-400::placeholder {
            color: #64748b !important;
        }
    </style>
</head>
<body class="h-full antialiased font-sans">
    {{-- Inertia mounts Vue here --}}
    @inertia

    {{-- ── Alpine.js for dark mode toggling ────────────────────────── --}}
    <script>
        // Apply theme immediately to prevent flash
        (function() {
            const saved = localStorage.getItem('theme');
            if (saved === 'dark' || (!saved && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();

        // Expose dark mode toggle globally so Vue components can call it
        window.__toggleDark = function() {
            const isDark = document.documentElement.classList.contains('dark');
            if (isDark) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        };
        
        window.__isDark = function() {
            return document.documentElement.classList.contains('dark');
        };

        // Also expose a method to set dark mode programmatically
        window.__setDark = function(isDark) {
            if (isDark) {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
        };

        // Listen for system theme changes (optional)
        if (window.matchMedia) {
            const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            darkModeMediaQuery.addEventListener('change', function(e) {
                // Only change if user hasn't explicitly set a preference
                if (!localStorage.getItem('theme')) {
                    if (e.matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            });
        }
    </script>
</body>
</html>