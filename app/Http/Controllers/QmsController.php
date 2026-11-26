<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QmsController extends Controller
{
    public function index(): View
    {
        $data = [
            'documents' => DB::table('quality_documents')->latest()->get(),
            'processes' => DB::table('quality_processes')->latest()->get(),
            'risks' => DB::table('risk_register')->latest()->get(),
            'actions' => DB::table('corrective_actions')->latest()->get(),
            'audits' => DB::table('internal_audits')->latest()->get(),
            'improvements' => DB::table('improvement_records')->latest()->get(),
            'objectives' => DB::table('quality_objectives')->latest()->get(),
            'feedback' => DB::table('customer_feedback')->latest()->get(),
            'reviews' => DB::table('management_reviews')->latest()->get(),
            'users' => DB::table('users')->orderBy('name')->get(['id', 'name']),
        ];
        return view('qms.index', $data);
    }

    public function store(Request $request, string $section): RedirectResponse
    {
        $tables = [
            'documents' => 'quality_documents',
            'processes' => 'quality_processes',
            'risks' => 'risk_register',
            'actions' => 'corrective_actions',
            'audits' => 'internal_audits',
            'improvements' => 'improvement_records',
            'objectives' => 'quality_objectives',
            'feedback' => 'customer_feedback',
            'reviews' => 'management_reviews',
        ];
        abort_unless(isset($tables[$section]), 404);
        $fields = match ($section) {
            'documents' => [
                'reference_number' => ['required', 'string', 'max:100'],
                'title' => ['required', 'string', 'max:255'],
                'category' => ['required', 'string', 'max:100'],
                'version' => ['required', 'string', 'max:50'],
                'approval_status' => ['required', 'string', 'in:Draft,In review,Approved,Obsolete'],
                'status' => ['required', 'string', 'in:Active,Obsolete'],
                'review_date' => ['nullable', 'date'],
                'owner_id' => ['nullable', 'integer', 'exists:users,id'],
                'change_note' => ['nullable', 'string', 'max:2000'],
                'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            ],
            'processes' => [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'inputs' => ['nullable', 'string', 'max:5000'],
                'outputs' => ['nullable', 'string', 'max:5000'],
                'status' => ['required', 'string', 'in:Active,Under review,Inactive'],
                'owner_id' => ['nullable', 'integer', 'exists:users,id'],
            ],
            'risks' => [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string', 'max:5000'],
                'level' => ['required', 'string', 'in:Low,Medium,High'],
                'impact' => ['nullable', 'string', 'max:5000'],
                'mitigation' => ['nullable', 'string', 'max:5000'],
                'status' => ['required', 'string', 'in:Open,Monitoring,Mitigated,Closed'],
                'responsible_id' => ['nullable', 'integer', 'exists:users,id'],
            ],
            'actions' => [
                'reference_number' => ['required', 'string', 'max:100'],
                'problem' => ['required', 'string', 'max:5000'],
                'root_cause' => ['nullable', 'string', 'max:5000'],
                'action' => ['nullable', 'string', 'max:5000'],
                'target_date' => ['nullable', 'date'],
                'status' => ['required', 'string', 'in:Open,In progress,Completed,Verified'],
                'verification' => ['nullable', 'string', 'max:5000'],
                'responsible_id' => ['nullable', 'integer', 'exists:users,id'],
            ],
            'audits' => [
                'title' => ['required', 'string', 'max:255'],
                'scope' => ['required', 'string', 'max:5000'],
                'criteria' => ['nullable', 'string', 'max:5000'],
                'scheduled_date' => ['nullable', 'date'],
                'status' => ['required', 'string', 'in:Scheduled,In progress,Completed'],
                'findings' => ['nullable', 'string', 'max:5000'],
                'auditor_id' => ['nullable', 'integer', 'exists:users,id'],
            ],
            'improvements' => [
                'title' => ['required', 'string', 'max:255'],
                'proposal' => ['required', 'string', 'max:5000'],
                'target_date' => ['nullable', 'date'],
                'status' => ['required', 'string', 'in:Proposed,In progress,Implemented,Reviewed'],
                'effectiveness' => ['nullable', 'string', 'max:5000'],
                'responsible_id' => ['nullable', 'integer', 'exists:users,id'],
            ],
            'objectives' => [
                'title' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:5000'],
                'target_value' => ['nullable', 'numeric'],
                'current_value' => ['nullable', 'numeric'],
                'unit' => ['nullable', 'string', 'max:50'],
                'owner_id' => ['nullable', 'integer', 'exists:users,id'],
                'due_date' => ['nullable', 'date'],
                'status' => ['required', 'string', 'in:Active,Behind,Completed,Cancelled'],
            ],
            'feedback' => [
                'reference_number' => ['required', 'string', 'max:100', 'unique:customer_feedback,reference_number'],
                'source' => ['nullable', 'string', 'max:100'],
                'category' => ['nullable', 'string', 'max:100'],
                'feedback' => ['required', 'string', 'max:5000'],
                'received_date' => ['nullable', 'date'],
                'response' => ['nullable', 'string', 'max:5000'],
                'status' => ['required', 'string', 'in:Open,In progress,Resolved,Closed'],
            ],
            'reviews' => [
                'review_date' => ['required', 'date'],
                'attendees' => ['nullable', 'string', 'max:5000'],
                'quality_objectives' => ['nullable', 'string', 'max:5000'],
                'audit_results' => ['nullable', 'string', 'max:5000'],
                'customer_feedback' => ['nullable', 'string', 'max:5000'],
                'process_performance' => ['nullable', 'string', 'max:5000'],
                'nonconformities' => ['nullable', 'string', 'max:5000'],
                'risks_opportunities' => ['nullable', 'string', 'max:5000'],
                'recommendations' => ['nullable', 'string', 'max:5000'],
            ],
        };
        $validated = $request->validate($fields);
        $now = now();
        $changeNote = $validated['change_note'] ?? null;
        unset($validated['change_note'], $validated['file']);
        $filePath = $section === 'documents'
            ? $request->file('file')?->store('qms/documents', 'local')
            : null;
        if ($section === 'documents') {
            $validated['file_path'] = $filePath;
        }
        if ($section === 'documents' && $validated['approval_status'] === 'Approved') {
            $validated['approved_at'] = $now;
            $validated['approved_by'] = $request->user()->id;
        }
        if ($section === 'reviews') {
            $validated['created_by'] = $request->user()->id;
        }
        $record = $validated + ['created_at' => $now, 'updated_at' => $now];
        DB::transaction(function () use ($section, $tables, $record, $validated, $changeNote, $filePath, $request, $now): void {
            $recordId = DB::table($tables[$section])->insertGetId($record);

            if ($section === 'documents') {
                DB::table('quality_document_revisions')->insert([
                    'quality_document_id' => $recordId,
                    'version' => $validated['version'],
                    'change_note' => $changeNote,
                    'file_path' => $filePath,
                    'created_by' => $request->user()->id,
                    'approved_by' => $validated['approved_by'] ?? null,
                    'approved_at' => $validated['approved_at'] ?? null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('qms_record_history')->insert([
                'record_type' => $section,
                'record_id' => $recordId,
                'user_id' => $request->user()->id,
                'action' => 'created',
                'snapshot' => json_encode($record, JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
        AuditLog::record('transaction', 'Created a QMS '.$section.' record.', $request->user());

        return back()->with('success', 'QMS record created.');
    }

    public function update(Request $request, string $section, int $id): RedirectResponse
    {
        $tables = $this->tables();
        abort_unless(isset($tables[$section]), 404);
        $record = DB::table($tables[$section])->where('id', $id)->first();
        abort_if($record === null, 404);

        $statusRules = [
            'documents' => [
                'status' => ['sometimes', 'required', 'in:Active,Obsolete'],
                'approval_status' => ['sometimes', 'required', 'in:Draft,In review,Approved,Obsolete'],
            ],
            'processes' => ['status' => ['required', 'in:Active,Under review,Inactive']],
            'risks' => ['status' => ['required', 'in:Open,Monitoring,Mitigated,Closed']],
            'actions' => ['status' => ['required', 'in:Open,In progress,Completed,Verified']],
            'audits' => ['status' => ['required', 'in:Scheduled,In progress,Completed']],
            'improvements' => ['status' => ['required', 'in:Proposed,In progress,Implemented,Reviewed']],
            'objectives' => ['status' => ['required', 'in:Active,Behind,Completed,Cancelled']],
            'feedback' => ['status' => ['required', 'in:Open,In progress,Resolved,Closed']],
            'reviews' => [],
        ];
        $rules = $statusRules[$section];
        if ($section === 'documents') {
            $rules['change_note'] = ['nullable', 'string', 'max:2000'];
            $request->validate([
                'status' => ['required_without:approval_status', 'nullable', 'in:Active,Obsolete'],
                'approval_status' => ['required_without:status', 'nullable', 'in:Draft,In review,Approved,Obsolete'],
                'change_note' => $rules['change_note'],
            ]);
        } elseif ($section === 'reviews') {
            abort(422, 'Management review records are immutable after creation.');
        } else {
            $request->validate($rules);
        }

        $changes = $request->only(array_keys($rules));
        if ($section === 'documents') {
            $changes = $request->only(['status', 'approval_status']);
            if (array_key_exists('approval_status', $changes)) {
                $changes['approved_at'] = $changes['approval_status'] === 'Approved' ? now() : null;
                $changes['approved_by'] = $changes['approval_status'] === 'Approved' ? $request->user()->id : null;
            }
        }
        $now = now();
        $changeNote = $request->input('change_note');

        DB::transaction(function () use ($section, $id, $tables, $record, $changes, $changeNote, $request, $now): void {
            DB::table('qms_record_history')->insert([
                'record_type' => $section,
                'record_id' => $id,
                'user_id' => $request->user()->id,
                'action' => 'updated',
                'snapshot' => json_encode([
                    'before' => $record,
                    'change_note' => $changeNote,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table($tables[$section])->where('id', $id)->update($changes + ['updated_at' => $now]);

            if ($section === 'documents' && array_key_exists('approval_status', $changes)) {
                DB::table('quality_document_revisions')
                    ->where('quality_document_id', $id)
                    ->where('version', $record->version)
                    ->update([
                        'approved_by' => $changes['approved_by'],
                        'approved_at' => $changes['approved_at'],
                        'updated_at' => $now,
                    ]);
            }
        });

        AuditLog::record('transaction', 'Updated a QMS '.$section.' record.', $request->user());

        return back()->with('success', 'QMS record updated.');
    }

    public function destroy(Request $request, string $section, int $id): RedirectResponse
    {
        $tables = $this->tables();
        abort_unless(isset($tables[$section]), 404);
        $record = DB::table($tables[$section])->where('id', $id)->first();
        abort_if($record === null, 404);
        abort_if($section === 'reviews', 422, 'Management review records are immutable after creation.');
        $documentFiles = [];
        if ($section === 'documents') {
            $documentFiles = DB::table('quality_document_revisions')
                ->where('quality_document_id', $id)
                ->pluck('file_path')
                ->filter()
                ->unique()
                ->all();
            if (filled($record->file_path)) {
                $documentFiles[] = $record->file_path;
            }
        }

        DB::transaction(function () use ($section, $id, $tables, $record, $request): void {
            DB::table('qms_record_history')->insert([
                'record_type' => $section,
                'record_id' => $id,
                'user_id' => $request->user()->id,
                'action' => 'deleted',
                'snapshot' => json_encode($record, JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            DB::table($tables[$section])->where('id', $id)->delete();
        });

        if ($documentFiles !== []) {
            Storage::disk('local')->delete($documentFiles);
        }
        AuditLog::record('transaction', 'Deleted a QMS '.$section.' record.', $request->user());

        return back()->with('success', 'QMS record deleted.');
    }

    public function document(Request $request, int $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $record = DB::table('quality_documents')->where('id', $document)->first();
        abort_if($record === null || blank($record->file_path), 404);
        abort_unless(Storage::disk('local')->exists($record->file_path), 404);

        AuditLog::record('access', 'Downloaded controlled QMS document '.$record->reference_number.'.', $request->user());

        return Storage::disk('local')->download(
            $record->file_path,
            Str::slug($record->reference_number.'-'.$record->version).'.'.pathinfo($record->file_path, PATHINFO_EXTENSION),
            ['Cache-Control' => 'private, no-store']
        );
    }

    public function addDocumentRevision(Request $request, int $document): RedirectResponse
    {
        $record = DB::table('quality_documents')->where('id', $document)->first();
        abort_if($record === null, 404);
        $validated = $request->validate([
            'version' => [
                'required',
                'string',
                'max:50',
                Rule::unique('quality_document_revisions', 'version')
                    ->where(fn ($query) => $query->where('quality_document_id', $document)),
            ],
            'change_note' => ['required', 'string', 'max:2000'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);
        $filePath = $request->file('file')?->store('qms/documents', 'local');
        $now = now();

        DB::transaction(function () use ($validated, $record, $document, $filePath, $request, $now): void {
            DB::table('qms_record_history')->insert([
                'record_type' => 'documents',
                'record_id' => $document,
                'user_id' => $request->user()->id,
                'action' => 'revision_created',
                'snapshot' => json_encode([
                    'before' => $record,
                    'new_version' => $validated['version'],
                    'change_note' => $validated['change_note'],
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('quality_documents')->where('id', $document)->update([
                'version' => $validated['version'],
                'file_path' => $filePath,
                'approval_status' => 'Draft',
                'approved_at' => null,
                'approved_by' => null,
                'updated_at' => $now,
            ]);
            DB::table('quality_document_revisions')->insert([
                'quality_document_id' => $document,
                'version' => $validated['version'],
                'change_note' => $validated['change_note'],
                'file_path' => $filePath,
                'created_by' => $request->user()->id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        AuditLog::record('transaction', 'Created QMS document revision '.$record->reference_number.' '.$validated['version'].'.', $request->user());

        return back()->with('success', 'Document revision created as a draft.');
    }

    public function documentRevision(Request $request, int $document, int $revision): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $revisionRecord = DB::table('quality_document_revisions')
            ->where('id', $revision)
            ->where('quality_document_id', $document)
            ->first();
        abort_if($revisionRecord === null || blank($revisionRecord->file_path), 404);
        abort_unless(Storage::disk('local')->exists($revisionRecord->file_path), 404);
        $documentRecord = DB::table('quality_documents')->where('id', $document)->first();
        abort_if($documentRecord === null, 404);

        AuditLog::record('access', 'Downloaded controlled QMS document revision '.$documentRecord->reference_number.' '.$revisionRecord->version.'.', $request->user());

        return Storage::disk('local')->download(
            $revisionRecord->file_path,
            Str::slug($documentRecord->reference_number.'-'.$revisionRecord->version).'.'.pathinfo($revisionRecord->file_path, PATHINFO_EXTENSION),
            ['Cache-Control' => 'private, no-store']
        );
    }

    public function history(string $section, int $id): View
    {
        $tables = $this->tables();
        abort_unless(isset($tables[$section]), 404);
        $historyQuery = DB::table('qms_record_history')
            ->leftJoin('users', 'users.id', '=', 'qms_record_history.user_id')
            ->where('record_type', $section)
            ->where('record_id', $id);
        abort_unless(DB::table($tables[$section])->where('id', $id)->exists() || $historyQuery->exists(), 404);
        $history = $historyQuery
            ->orderByDesc('qms_record_history.created_at')
            ->get(['qms_record_history.*', 'users.name as user_name']);

        $revisions = $section === 'documents'
            ? DB::table('quality_document_revisions')
                ->where('quality_document_id', $id)
                ->orderByDesc('created_at')
                ->get()
            : collect();

        return view('qms.history', compact('history', 'revisions', 'section', 'id'));
    }

    private function tables(): array
    {
        return [
            'documents' => 'quality_documents',
            'processes' => 'quality_processes',
            'risks' => 'risk_register',
            'actions' => 'corrective_actions',
            'audits' => 'internal_audits',
            'improvements' => 'improvement_records',
            'objectives' => 'quality_objectives',
            'feedback' => 'customer_feedback',
            'reviews' => 'management_reviews',
        ];
    }
}
