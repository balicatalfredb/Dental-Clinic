<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User report</title>
    <style>
        * { box-sizing: border-box; }
        body { max-width: 1100px; margin: 0 auto; padding: 32px 24px; color: #19324a; font: 14px/1.5 Arial, sans-serif; }
        .report-header { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; margin-bottom: 24px; }
        h1 { margin: 0 0 8px; font: 400 34px Georgia, serif; }
        .report-meta { margin: 0; color: #708296; font-size: 12px; }
        .print-button { min-height: 42px; padding: 10px 16px; color: #fff; background: #087f73; border: 0; border-radius: 6px; cursor: pointer; font: 700 12px Arial, sans-serif; }
        .print-button:hover { background: #06685f; }
        .table-scroll { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 11px 12px; border: 1px solid #d5e0e7; text-align: left; }
        th { color: #344c60; background: #f5f9fa; font-size: 10px; letter-spacing: .6px; text-transform: uppercase; }
        @media (max-width: 620px) { body { padding: 20px 14px; } .report-header { flex-direction: column; } }
        @media print { body { max-width: none; padding: 0; } .print-button { display: none; } }
    </style>
</head>
<body>
    <header class="report-header">
        <div>
            <h1>User report</h1>
            <p class="report-meta">Generated {{ $generatedAt->format('Y-m-d H:i:s') }} by {{ $generatedBy }}</p>
        </div>
        <button class="print-button" type="button" onclick="window.print()">Print / Save PDF</button>
    </header>
    <div class="table-scroll">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Department</th><th>Role</th><th>Status</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->department ?: '—' }}</td><td>{{ $user->role }}</td><td>{{ $user->status }}</td></tr>
                @empty
                    <tr><td colspan="5">No users match these filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
