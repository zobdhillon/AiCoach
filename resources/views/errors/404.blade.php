<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Page Not Found — Rehearse</title>
        <script>
            const saved = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (saved ? saved === 'dark' : prefersDark) {
                document.documentElement.classList.add('dark');
            }
        </script>
        @vite('resources/css/app.css')
    </head>

    <body
        style="background: var(--bg); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Plus Jakarta Sans', sans-serif;">
        <div style="text-align: center; padding: 2rem; max-width: 420px;">
            <div
                style="display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 16px; background: var(--accent-bg); margin-bottom: 1.5rem;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--accent)"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
            </div>
            <h1 style="font-size: 2rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem;">404</h1>
            <p style="font-size: 0.95rem; font-weight: 600; color: var(--text-2); margin-bottom: 0.5rem;">Page not found
            </p>
            <p style="font-size: 0.85rem; color: var(--text-3); margin-bottom: 1.5rem; line-height: 1.5;">
                The page you're looking for doesn't exist or may have been moved.
            </p>
            <a href="/dashboard"
                style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.5rem; border-radius: 0.75rem; background: var(--gradient-primary); color: white; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
                Back to Dashboard
            </a>
        </div>
    </body>

</html>
