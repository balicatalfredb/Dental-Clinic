<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>@yield('title', 'My App')</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('dental-theme');
            const theme = savedTheme || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            document.documentElement.dataset.theme = theme;
        })();
    </script>
    <style>
        :root { color-scheme: light; }
        html[data-theme="dark"] { color-scheme: dark; --ink: #e4edf3; --muted: #b0c1cc; --line: #3a5263; --paper: #0f2535; --surface: #183448; --surface-raised: #204257; --field: #102938; --mint: #163d3a; }
        html[data-theme="dark"] body { --ink: #e4edf3; --muted: #b0c1cc; --line: #3a5263; --paper: #0f2535; --mint: #163d3a; background: var(--paper); color: var(--ink); }
        .site-nav { box-sizing: border-box; display: flex; flex-wrap: wrap; align-items: center; gap: 18px 30px; width: 100%; min-width: 0; min-height: 74px; padding: 12px clamp(18px, 5vw, 72px); color: white; background: #123752; font-family: Arial, sans-serif; }
        .site-brand { display: inline-flex; flex: 0 0 auto; align-items: center; gap: 9px; color: white; font: 700 23px Georgia, serif; text-decoration: none; white-space: nowrap; }
        .site-brand-logo, .site-brand-mark { display: inline-grid; width: 34px; height: 34px; flex: 0 0 34px; border-radius: 50%; object-fit: contain; place-items: center; }
        .site-brand-mark { color: #123752; background: #dff4ed; font: 700 20px Arial, sans-serif; }
        .site-nav-links { display: flex; flex: 1 1 520px; flex-wrap: wrap; align-items: center; gap: 4px 8px; min-width: 0; margin: 0; padding: 0; list-style: none; }
        .site-nav-links a { display: block; padding: 10px 11px; border-radius: 4px; color: #d3e2e8; font-size: 13px; line-height: 1.25; text-decoration: none; white-space: nowrap; transition: color .18s ease, background-color .18s ease, transform .18s ease; }
        .site-nav-links a:hover { transform: translateY(-1px); }
        .site-nav-links a:hover, .site-nav-links a[aria-current="page"] { color: white; background: #204963; }
        @view-transition { navigation: auto; }
        ::view-transition-old(root) { animation: site-page-exit .12s ease-in both; }
        ::view-transition-new(root) { animation: site-page-enter .16s ease-out both; }
        @keyframes site-page-exit {
            to { opacity: 0; transform: translateY(-4px); }
        }
        @keyframes site-page-enter {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .site-nav-account { display: flex; flex-direction: column; gap: 4px; min-width: max-content; font-family: Arial, sans-serif; text-align: right; }
        .site-nav-greeting { color: white; font-size: 13px; font-weight: 700; }
        .site-nav-time { color: #c8e8df; font-size: 11px; white-space: nowrap; }
        .site-nav form { flex: 0 0 auto; margin-left: auto; }
        .site-nav button { padding: 9px 13px; color: white; background: transparent; border: 1px solid #7893a2; border-radius: 4px; cursor: pointer; font: 13px Arial, sans-serif; white-space: nowrap; }
        .site-nav button:hover { background: #204963; }
        .password-field { position: relative; }
        .password-field > input { padding-right: 78px !important; }
        .password-toggle { position: absolute; top: 50%; right: 8px; z-index: 1; transform: translateY(-50%); padding: 6px 8px; color: #087f73; background: transparent; border: 0; border-radius: 4px; cursor: pointer; font: 700 11px Arial, sans-serif; }
        .password-toggle:hover { color: #06685f; background: rgba(8, 127, 115, .08); }
        .password-toggle:focus-visible { outline: 2px solid #087f73; outline-offset: 2px; }
        .recovery-page { box-sizing: border-box; display: grid; min-height: 100vh; padding: 28px 18px; place-items: center; background: #f5f9fa; font-family: Arial, sans-serif; }
        .recovery-card { box-sizing: border-box; width: min(480px, 100%); padding: clamp(24px, 5vw, 42px); background: #fff; border: 1px solid #e3ebf1; border-radius: 12px; box-shadow: 0 18px 48px rgba(18, 55, 82, .09); }
        .recovery-brand { display: inline-flex; align-items: center; gap: 10px; margin-bottom: 30px; color: #123752; font: 700 20px Georgia, serif; text-decoration: none; }
        .recovery-mark { display: inline-grid; width: 34px; height: 34px; color: #102938; background: #73d5c1; border-radius: 50%; place-items: center; font: 700 18px Arial, sans-serif; }
        .recovery-logo { width: 34px; height: 34px; padding: 3px; background: white; border-radius: 50%; object-fit: contain; }
        .recovery-eyebrow { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; }
        .recovery-card h1 { margin: 0 0 10px; color: #19324a; font: 400 clamp(28px, 5vw, 36px) Georgia, serif; }
        .recovery-lead { margin: 0 0 24px; color: #708296; font-size: 14px; line-height: 1.55; }
        .recovery-field { margin-bottom: 17px; }
        .recovery-field label { display: block; margin-bottom: 7px; color: #19324a; font-size: 12px; font-weight: 700; }
        .recovery-field > input, .recovery-field .password-field > input { box-sizing: border-box; width: 100%; min-height: 46px; padding: 11px 13px; color: #19324a; background: #fbfdfd; border: 1px solid #d5e0e7; border-radius: 7px; font: 14px Arial, sans-serif; }
        .recovery-field .password-field > input { padding-right: 78px; }
        .recovery-hint { display: block; margin: -8px 0 17px; color: #708296; font-size: 12px; line-height: 1.5; }
        .recovery-submit { width: 100%; min-height: 48px; padding: 12px 16px; color: #fff; background: #087f73; border: 0; border-radius: 7px; cursor: pointer; font: 700 13px Arial, sans-serif; transition: background .15s ease, transform .15s ease; }
        .recovery-submit:hover { background: #06675e; transform: translateY(-1px); }
        .recovery-back { display: block; margin-top: 20px; color: #087f73; font-size: 13px; font-weight: 700; text-align: center; text-decoration: none; }
        .recovery-back:hover { text-decoration: underline; }
        .recovery-errors { margin: 0 0 18px; padding: 12px 14px 12px 32px; color: #a74435; background: #fff0ed; border-radius: 6px; font-size: 13px; line-height: 1.5; }
        .recovery-status { margin: 0 0 18px; padding: 12px 14px; color: #126653; background: #e4f5ef; border-radius: 6px; font-size: 13px; line-height: 1.5; }
        html[data-theme="dark"] body .profile-panel,
        html[data-theme="dark"] body .table-panel,
        html[data-theme="dark"] body .panel,
        html[data-theme="dark"] body .stat-card,
        html[data-theme="dark"] body .edit-panel,
        html[data-theme="dark"] body .form-panel,
        html[data-theme="dark"] body .backup-list,
        html[data-theme="dark"] body .table-wrap,
        html[data-theme="dark"] body .register-form-wrap { background: var(--surface); border-color: var(--line); }
        html[data-theme="dark"] body input,
        html[data-theme="dark"] body select,
        html[data-theme="dark"] body textarea { color: var(--ink); background: var(--field); border-color: var(--line); }
        html[data-theme="dark"] body input::placeholder,
        html[data-theme="dark"] body textarea::placeholder { color: #9aafbd; opacity: 1; }
        input[type="file"]::file-selector-button { margin-right: 12px; padding: 9px 14px; color: #fff; background: #087f73; border: 0; border-radius: 5px; cursor: pointer; font: 700 12px Arial, sans-serif; transition: background .15s ease, transform .15s ease; }
        input[type="file"]::file-selector-button:hover { background: #06685f; transform: translateY(-1px); }
        input[type="file"]:focus-visible { outline: 3px solid rgba(8, 127, 115, .3); outline-offset: 2px; }
        html[data-theme="dark"] body input[type="file"]::file-selector-button { color: #e4edf3; background: #176f67; border: 1px solid #4d9d90; }
        html[data-theme="dark"] body input[type="file"]::file-selector-button:hover { background: #218477; }
        html[data-theme="dark"] body th { color: var(--muted); background: var(--field); }
        html[data-theme="dark"] body .edit-header,
        html[data-theme="dark"] body .edit-tabs,
        html[data-theme="dark"] body .edit-footer,
        html[data-theme="dark"] body td,
        html[data-theme="dark"] body .table-toolbar,
        html[data-theme="dark"] body .appointment,
        html[data-theme="dark"] body .task,
        html[data-theme="dark"] body .backup-row { border-color: var(--line); }
        html[data-theme="dark"] body :is(td, th, h1, h2, h3, p, li, label) { color: var(--ink); }
        html[data-theme="dark"] body :is(.subtext, .muted, .heading-copy, .lead, .patient-detail, .stat-label, .date-label) { color: var(--muted); }
        html[data-theme="dark"] body :is(.event, .notice, .stat-note) { color: #73d5c1; }
        html[data-theme="dark"] body .errors { color: #ff9a8b; }
        html[data-theme="dark"] body .edit-tab,
        html[data-theme="dark"] body .close-edit,
        html[data-theme="dark"] body .date-label { color: var(--muted); }
        html[data-theme="dark"] body .cancel-btn { color: #a4e1d3; background: var(--surface-raised); border-color: var(--line); }
        html[data-theme="dark"] body .role,
        html[data-theme="dark"] body .avatar { color: #a4e1d3; background: #21463f; }
        html[data-theme="dark"] body :is(.brand-mark, .auth-mark, .register-mark) { color: #102938; background: #73d5c1; }
        html[data-theme="dark"] body .download,
        html[data-theme="dark"] body .actions,
        html[data-theme="dark"] body .security-link { color: #73d5c1; }
        html[data-theme="dark"] body .register-page,
        html[data-theme="dark"] body .register-form-wrap { color: var(--ink); background: var(--surface); }
        html[data-theme="dark"] body .field label,
        html[data-theme="dark"] body .edit-field label,
        html[data-theme="dark"] body label { color: var(--ink); }
        html[data-theme="dark"] body a:not(.site-brand):not(.site-nav-links a) { color: #73d5c1; }
        html[data-theme="dark"] body .site-nav a { color: #d3e2e8; }
        html[data-theme="dark"] body .site-nav a:hover,
        html[data-theme="dark"] body .site-nav a[aria-current="page"] { color: white; }
        html[data-theme="dark"] body .password-toggle { color: #73d5c1; }
        html[data-theme="dark"] body .password-toggle:hover { color: #a4e1d3; background: rgba(115, 213, 193, .12); }
        html[data-theme="dark"] body .recovery-page { background: #0f2535; }
        html[data-theme="dark"] body .recovery-card { background: #183448; border-color: #3a5263; box-shadow: 0 18px 48px rgba(0, 0, 0, .2); }
        html[data-theme="dark"] body .recovery-brand { color: #e4edf3; }
        html[data-theme="dark"] body .recovery-card h1, html[data-theme="dark"] body .recovery-field label { color: #e4edf3; }
        html[data-theme="dark"] body .recovery-lead, html[data-theme="dark"] body .recovery-hint { color: #b0c1cc; }
        html[data-theme="dark"] body .recovery-field > input, html[data-theme="dark"] body .recovery-field .password-field > input { color: #e4edf3; background: #102938; border-color: #3a5263; }
        html[data-theme="dark"] body .recovery-field input::placeholder { color: #9aafbd; opacity: 1; }
        html[data-theme="dark"] body .recovery-back { color: #73d5c1; }
        html[data-theme="dark"] body .recovery-errors { color: #ff9a8b; background: #432d31; }
        html[data-theme="dark"] body :is(.settings-alert:not(.settings-alert-error), .qms-alert:not(.qms-alert-error), .backup-alert:not(.backup-alert-error), .recovery-status) { color: #a4e1d3; background: #163d3a; }
        html[data-theme="dark"] body :is(.settings-alert-error, .qms-alert-error, .backup-alert-error, .reports-errors, .audit-errors, .role-errors, .form-errors, .error-list) { color: #ffb4a9; background: #432d31; }
        .pagination svg { width: 1rem; height: 1rem; vertical-align: middle; }
        .pagination { flex-wrap: wrap; gap: 4px; }
        html[data-theme="dark"] body .pagination .page-link { color: #a4e1d3; background: var(--surface); border-color: var(--line); }
        html[data-theme="dark"] body .pagination .page-item.active .page-link { color: #fff; background: #087f73; border-color: #087f73; }
        html[data-theme="dark"] body .pagination .page-item.disabled .page-link { color: #8299a8; background: var(--field); border-color: var(--line); }
        @media (max-width: 760px) {
            .site-nav { gap: 8px 14px; padding: 12px 18px; }
            .site-nav-links { flex-basis: 100%; gap: 2px 4px; }
            .site-nav-links a { padding: 9px; }
            .site-nav-account { flex: 1 1 auto; text-align: left; }
            .site-nav form { margin-left: auto; }
        }
        @media (prefers-reduced-motion: reduce) {
            ::view-transition-old(root), ::view-transition-new(root) { animation: none; }
        }
        .theme-toggle { position: fixed; right: 22px; bottom: 22px; z-index: 20; width: 44px; height: 44px; display: grid; place-items: center; color: #19324a; background: #fff; border: 1px solid #dbe6ec; border-radius: 50%; box-shadow: 0 8px 24px rgba(18,55,82,.18); cursor: pointer; font-size: 20px; }
        html[data-theme="dark"] .theme-toggle { color: #dceaf2; background: #183448; border-color: #314657; }
    </style>
</head>
<body>
    @yield('content')
    <button class="theme-toggle" id="themeToggle" type="button" aria-label="Switch to dark mode" title="Switch theme"></button>
    <script>
        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-password-toggle]');
            if (!button) return;

            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const showPassword = input.type === 'password';
            input.type = showPassword ? 'text' : 'password';
            button.textContent = showPassword ? 'Hide' : 'Show';
            button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
            button.setAttribute('aria-label', `${showPassword ? 'Hide' : 'Show'} ${input.labels?.[0]?.textContent.trim() || 'password'}`);
        });

        (() => {
            const root = document.documentElement;
            const button = document.getElementById('themeToggle');
            const updateThemeButton = () => {
                const dark = root.dataset.theme === 'dark';
                button.textContent = dark ? '☀' : '☾';
                button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
            };
            button.addEventListener('click', () => {
                root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('dental-theme', root.dataset.theme);
                updateThemeButton();
            });
            updateThemeButton();
        })();
    </script>
</body>
</html>
