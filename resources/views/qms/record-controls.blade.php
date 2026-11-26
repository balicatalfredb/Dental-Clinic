@php
    $statusOptions = match ($section) {
        'documents' => ['Active', 'Obsolete'],
        'processes' => ['Active', 'Under review', 'Inactive'],
        'risks' => ['Open', 'Monitoring', 'Mitigated', 'Closed'],
        'actions' => ['Open', 'In progress', 'Completed', 'Verified'],
        'audits' => ['Scheduled', 'In progress', 'Completed'],
        'improvements' => ['Proposed', 'In progress', 'Implemented', 'Reviewed'],
        'objectives' => ['Active', 'Behind', 'Completed', 'Cancelled'],
        'feedback' => ['Open', 'In progress', 'Resolved', 'Closed'],
        default => [],
    };
@endphp
<div class="qms-record-controls">
    <a href="{{ route('qms.history', [$section, $row->id]) }}">History</a>
    @if ($section === 'documents' && filled($row->file_path))
        <a href="{{ route('qms.document.download', $row->id) }}">Download document</a>
    @endif
    @if ($section !== 'reviews')
        <form method="POST" action="{{ route('qms.update', [$section, $row->id]) }}">
            @csrf
            @method('PATCH')
            @if ($section === 'documents')
                <label class="visually-hidden" for="approval-{{ $row->id }}">Approval status</label>
                <select id="approval-{{ $row->id }}" name="approval_status">
                    @foreach (['Draft', 'In review', 'Approved', 'Obsolete'] as $approvalStatus)
                        <option value="{{ $approvalStatus }}" @selected($row->approval_status === $approvalStatus)>{{ $approvalStatus }}</option>
                    @endforeach
                </select>
            @endif
            <label class="visually-hidden" for="record-status-{{ $section }}-{{ $row->id }}">Status</label>
            <select id="record-status-{{ $section }}-{{ $row->id }}" name="status">
                @foreach ($statusOptions as $statusOption)
                    <option value="{{ $statusOption }}" @selected($row->status === $statusOption)>{{ $statusOption }}</option>
                @endforeach
            </select>
            <button type="submit">Update</button>
        </form>
        <form method="POST" action="{{ route('qms.destroy', [$section, $row->id]) }}" onsubmit="return confirm('Delete this quality record?')">
            @csrf
            @method('DELETE')
            <button class="qms-delete" type="submit">Delete</button>
        </form>
    @endif
</div>
