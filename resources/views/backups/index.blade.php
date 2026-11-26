@extends('layout.app')
@section('title', 'Backups - Dental')
@section('content')
@include('layout.navigation')
<style>
    .backup-shell { max-width: 1150px; margin: 0 auto; padding: 42px 24px 64px; font-family: Arial, sans-serif; }
    .backup-heading { margin-bottom: 24px; }
    .backup-eyebrow { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    .backup-heading h1 { margin: 0 0 8px; color: #19324a; font: 400 clamp(30px, 4vw, 42px) Georgia, serif; }
    .backup-heading p:last-child { margin: 0; color: #708296; font-size: 14px; line-height: 1.5; }
    .backup-alert { margin: 0 0 18px; padding: 12px 16px; color: #126653; background: #e4f5ef; border-radius: 6px; font-size: 13px; }
    .backup-alert-error { color: #a74435; background: #fff0ed; }
    .backup-create-panel, .backup-history { background: #fff; border: 1px solid #e3ebf1; border-radius: 10px; box-shadow: 0 8px 26px rgba(25, 50, 74, .045); }
    .backup-create-panel { margin-bottom: 22px; padding: 20px; }
    .backup-create-panel h2, .backup-history-heading h2 { margin: 0; color: #19324a; font: 400 20px Georgia, serif; }
    .backup-create-panel > p { margin: 6px 0 16px; color: #708296; font-size: 13px; }
    .backup-actions { display: flex; flex-wrap: wrap; gap: 10px; }
    .backup-actions form { margin: 0; }
    .backup-button { display: inline-flex; min-height: 42px; box-sizing: border-box; align-items: center; justify-content: center; gap: 8px; padding: 10px 16px; color: #fff; background: #087f73; border: 1px solid #087f73; border-radius: 6px; cursor: pointer; font: 700 12px Arial, sans-serif; text-decoration: none; transition: background .15s ease, transform .15s ease, opacity .15s ease; }
    .backup-button:hover { color: #fff; background: #06685f; transform: translateY(-1px); }
    .backup-button:focus-visible { outline: 3px solid rgba(8, 127, 115, .28); outline-offset: 2px; }
    .backup-button:disabled { cursor: wait; opacity: .7; transform: none; }
    .backup-button-secondary { color: #087f73; background: #fff; border-color: #cbd9e1; }
    .backup-button-secondary:hover { color: #06685f; background: #f5f9fa; }
    .backup-button-danger { color: #a74435; background: transparent; border-color: #edc8c1; }
    .backup-button-danger:hover { color: #922f22; background: #fff0ed; }
    .backup-history-heading { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; align-items: center; padding: 18px 20px; border-bottom: 1px solid #e3ebf1; }
    .backup-history-count { color: #708296; font-size: 12px; }
    .backup-list { overflow: hidden; border-radius: 10px; }
    .backup-row { display: grid; grid-template-columns: minmax(200px, 1fr) 110px 170px 180px; gap: 14px; align-items: center; padding: 14px 20px; border-top: 1px solid #e3ebf1; font-size: 13px; }
    .backup-row:first-child { border-top: 0; }
    .backup-filename { min-width: 0; overflow-wrap: anywhere; color: #19324a; font-weight: 700; }
    .backup-meta { color: #708296; font-size: 12px; }
    .backup-row-actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
    .backup-row-actions .backup-button { min-height: 34px; padding: 7px 10px; font-size: 11px; }
    .backup-empty { padding: 26px 20px; color: #708296; text-align: center; font-size: 13px; }
    .backup-storage-note { margin: 14px 2px 0; color: #708296; font-size: 12px; line-height: 1.5; }
    html[data-theme="dark"] body .backup-heading h1, html[data-theme="dark"] body .backup-create-panel h2, html[data-theme="dark"] body .backup-history-heading h2 { color: var(--ink); }
    html[data-theme="dark"] body .backup-heading p:last-child, html[data-theme="dark"] body .backup-create-panel > p, html[data-theme="dark"] body .backup-history-count, html[data-theme="dark"] body .backup-meta, html[data-theme="dark"] body .backup-empty, html[data-theme="dark"] body .backup-storage-note { color: var(--muted); }
    html[data-theme="dark"] body .backup-create-panel, html[data-theme="dark"] body .backup-history { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .backup-row, html[data-theme="dark"] body .backup-history-heading { border-color: var(--line); }
    html[data-theme="dark"] body .backup-filename { color: var(--ink); }
    html[data-theme="dark"] body .backup-button-secondary { color: #a4e1d3; background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .backup-button-secondary:hover { color: #d3f4ea; background: var(--surface-raised); }
    html[data-theme="dark"] body .backup-button-danger { color: #ff9a8b; background: transparent; border-color: #70464a; }
    html[data-theme="dark"] body .backup-button-danger:hover { background: #432d31; }
    html[data-theme="dark"] body .backup-alert-error { color: #ff9a8b; background: #432d31; }
    @media (max-width: 760px) { .backup-shell { padding: 30px 18px 52px; } .backup-row { grid-template-columns: minmax(0, 1fr) auto; gap: 8px 12px; } .backup-row-actions { grid-column: 1 / -1; } }
    @media (max-width: 440px) { .backup-row { grid-template-columns: 1fr; } .backup-row-actions { grid-column: auto; } .backup-button { width: 100%; } .backup-row-actions .backup-button { width: auto; } }
</style>

<main class="backup-shell">
    <header class="backup-heading">
        <p class="backup-eyebrow">System administration</p>
        <h1>Backups</h1>
        <p>Create encrypted archives of the database or application files. Archives are kept in private storage.</p>
    </header>

    @if (session('success'))<p class="backup-alert" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())<ul class="backup-alert backup-alert-error" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif

    <section class="backup-create-panel" aria-labelledby="createBackupTitle">
        <h2 id="createBackupTitle">Create a backup</h2>
        <p>Backup generation can take a little time. Keep this page open until it completes.</p>
        <div class="backup-actions">
            <form action="{{ route('backups.store') }}" method="POST" data-backup-form>
                @csrf<input type="hidden" name="scope" value="database">
                <button class="backup-button" type="submit">Back up database</button>
            </form>
            <form action="{{ route('backups.store') }}" method="POST" data-backup-form>
                @csrf<input type="hidden" name="scope" value="system">
                <button class="backup-button backup-button-secondary" type="submit">Back up system files</button>
            </form>
        </div>
    </section>

    <section class="backup-history" aria-labelledby="backupHistoryTitle">
        <div class="backup-history-heading">
            <h2 id="backupHistoryTitle">Backup history</h2>
            <span class="backup-history-count">{{ $backups->count() }} {{ \Illuminate\Support\Str::plural('backup', $backups->count()) }}</span>
        </div>
        <div class="backup-list">
            @forelse ($backups as $backup)
                <div class="backup-row">
                    <strong class="backup-filename">{{ $backup['name'] }}</strong>
                    <span class="backup-meta">{{ number_format($backup['size'] / 1048576, 2) }} MB</span>
                    <time class="backup-meta" datetime="{{ date(DATE_ATOM, $backup['modified']) }}">{{ date('Y-m-d H:i', $backup['modified']) }}</time>
                    <div class="backup-row-actions">
                        <a class="backup-button backup-button-secondary" href="{{ route('backups.download', $backup['token']) }}">Download</a>
                        <form action="{{ route('backups.destroy', $backup['token']) }}" method="POST" onsubmit="return confirm('Delete this backup? This cannot be undone.')" style="display:inline">
                            @csrf @method('DELETE')
                            <button class="backup-button backup-button-danger" type="submit">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="backup-empty">No backups have been created yet.</p>
            @endforelse
        </div>
    </section>
    <p class="backup-storage-note">Backups are not publicly accessible. Store encryption keys separately and periodically test restoration in a safe environment.</p>
</main>
<script>
    document.querySelectorAll('[data-backup-form]').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (!button) return;
            button.disabled = true;
            button.textContent = 'Creating backup…';
            form.setAttribute('aria-busy', 'true');
        });
    });
</script>
@endsection
