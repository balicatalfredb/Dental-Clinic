@extends('layout.app')
@section('title', 'Quality record history')
@section('content')
@include('layout.navigation')
<style>
    .qms-history-shell { max-width: 1000px; margin: 0 auto; padding: 38px 24px 70px; font-family: Arial, sans-serif; color: #19324a; }
    .qms-history-shell h1 { margin: 0 0 18px; font: 400 34px Georgia, serif; }
    .qms-history-entry { margin: 12px 0; padding: 18px; background: #fff; border: 1px solid #e3ebf1; border-radius: 8px; }
    .qms-history-entry p { margin: 0 0 10px; color: #708296; font-size: 13px; }
    .qms-history-entry pre { max-height: 300px; margin: 0; padding: 12px; overflow: auto; color: #19324a; background: #f5f9fa; border-radius: 5px; font-size: 12px; white-space: pre-wrap; overflow-wrap: anywhere; }
    .qms-history-back { display: inline-block; margin-bottom: 20px; color: #087f73; }
    html[data-theme="dark"] body .qms-history-shell { color: var(--ink); }
    html[data-theme="dark"] body .qms-history-entry { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .qms-history-entry p { color: var(--muted); }
    html[data-theme="dark"] body .qms-history-entry pre { color: var(--ink); background: var(--field); }
</style>
<main class="qms-history-shell">
    <a class="qms-history-back" href="{{ route('qms.index') }}">← Back to quality records</a>
    <h1>Record history: {{ ucfirst($section) }} #{{ $id }}</h1>
    @if ($section === 'documents')
        <h2>Document revisions</h2>
        @forelse ($revisions as $revision)
            <article class="qms-history-entry">
                <p><strong>Version {{ $revision->version }}</strong> · {{ $revision->created_at }} · {{ $revision->approved_at ? 'Approved' : 'Not approved' }}</p>
                <p>{{ $revision->change_note ?: 'No change note' }}</p>
                @if ($revision->file_path)
                    <a href="{{ route('qms.document.revision.download', [$id, $revision->id]) }}">Download version {{ $revision->version }}</a>
                @endif
            </article>
        @empty
            <p>No document revisions are recorded.</p>
        @endforelse
    @endif
    @forelse ($history as $entry)
        <article class="qms-history-entry">
            <p><strong>{{ ucfirst($entry->action) }}</strong> · {{ $entry->created_at }} · {{ $entry->user_name ?: 'Deleted account' }}</p>
            <pre>{{ json_encode(json_decode($entry->snapshot, true, 512, JSON_THROW_ON_ERROR), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </article>
    @empty
        <p>No history entries exist for this record.</p>
    @endforelse
</main>
@endsection
