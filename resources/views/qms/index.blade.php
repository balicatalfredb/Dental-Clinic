@extends('layout.app')
@section('title', 'Quality management')
@section('content')
@include('layout.navigation')
<style>
    .qms-shell { max-width: 1180px; margin: 0 auto; padding: 42px 24px 72px; font-family: Arial, sans-serif; }
    .qms-heading { margin-bottom: 28px; }
    .qms-eyebrow { margin: 0 0 8px; color: #087f73; font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }
    .qms-heading h1 { margin: 0 0 8px; color: #19324a; font: 400 clamp(30px, 4vw, 42px) Georgia, serif; }
    .qms-heading p:last-child { margin: 0; color: #708296; font-size: 14px; }
    .qms-alert { margin: 0 0 20px; padding: 12px 16px; border-radius: 6px; background: #e4f5ef; color: #126653; font-size: 14px; }
    .qms-alert-error { background: #fff0ed; color: #a74435; }
    .qms-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
    .qms-card { min-width: 0; padding: 22px; background: #fff; border: 1px solid #e3ebf1; border-radius: 10px; box-shadow: 0 8px 26px rgba(25, 50, 74, .045); }
    .qms-card h2 { margin: 0 0 5px; color: #19324a; font: 400 22px Georgia, serif; }
    .qms-card > p { margin: 0 0 18px; color: #708296; font-size: 13px; }
    .qms-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px 12px; }
    .qms-field { min-width: 0; }
    .qms-field-wide { grid-column: 1 / -1; }
    .qms-field label { display: block; margin-bottom: 6px; color: #344c60; font-size: 12px; font-weight: 700; }
    .qms-field input, .qms-field select, .qms-field textarea { box-sizing: border-box; width: 100%; min-height: 40px; padding: 9px 10px; color: #19324a; background: #fff; border: 1px solid #d5e0e7; border-radius: 5px; font: 13px Arial, sans-serif; }
    .qms-field textarea { min-height: 76px; resize: vertical; }
    .qms-field input:focus, .qms-field select:focus, .qms-field textarea:focus { outline: 3px solid rgba(8, 127, 115, .16); border-color: #087f73; }
    .qms-submit { grid-column: 1 / -1; justify-self: start; padding: 10px 15px; color: #fff; background: #087f73; border: 0; border-radius: 5px; cursor: pointer; font-size: 13px; font-weight: 700; }
    .qms-submit:hover { background: #06685f; }
    .qms-records { display: grid; gap: 8px; margin-top: 18px; }
    .qms-record { padding: 10px 12px; background: #f5f9fa; border: 1px solid #e3ebf1; border-radius: 6px; font-size: 13px; }
    .qms-record strong { color: #19324a; }
    .qms-record span { display: block; margin-top: 4px; color: #708296; font-size: 12px; }
    .qms-record-controls { display: flex; flex-wrap: wrap; gap: 7px; align-items: center; margin-top: 10px; }
    .qms-record-controls form { display: flex; flex-wrap: wrap; gap: 6px; align-items: center; }
    .qms-record-controls a, .qms-record-controls button { padding: 6px 9px; color: #087f73; background: #fff; border: 1px solid #cbd9e1; border-radius: 4px; cursor: pointer; font: 700 11px Arial, sans-serif; text-decoration: none; }
    .qms-record-controls select { max-width: 150px; padding: 6px; color: #19324a; background: #fff; border: 1px solid #d5e0e7; border-radius: 4px; font-size: 11px; }
    .qms-record-controls .qms-delete { color: #a74435; }
    .qms-revision-form { display: grid; grid-template-columns: minmax(90px, .4fr) minmax(120px, 1fr) minmax(160px, 1.2fr) auto; gap: 7px; align-items: end; margin-top: 12px; padding-top: 10px; border-top: 1px solid #e3ebf1; }
    .qms-revision-form label { display: grid; gap: 4px; color: #708296; font-size: 10px; font-weight: 700; }
    .qms-revision-form input { width: 100%; box-sizing: border-box; padding: 7px; border: 1px solid #d5e0e7; border-radius: 4px; font-size: 11px; }
    .qms-revision-form button { padding: 8px; color: white; background: #087f73; border: 0; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: 700; }
    .qms-empty { color: #708296; font-size: 13px; }
    @media (max-width: 760px) { .qms-shell { padding: 30px 18px 56px; } .qms-grid { grid-template-columns: 1fr; } }
    @media (max-width: 480px) { .qms-card { padding: 18px 15px; } .qms-form { grid-template-columns: 1fr; } .qms-field-wide, .qms-submit { grid-column: auto; } }
    @media (max-width: 580px) { .qms-revision-form { grid-template-columns: 1fr 1fr; } }
    html[data-theme="dark"] body .qms-card { background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .qms-heading h1, html[data-theme="dark"] body .qms-card h2, html[data-theme="dark"] body .qms-record strong { color: var(--ink); }
    html[data-theme="dark"] body .qms-heading p:last-child, html[data-theme="dark"] body .qms-card > p, html[data-theme="dark"] body .qms-empty, html[data-theme="dark"] body .qms-record span { color: var(--muted); }
    html[data-theme="dark"] body .qms-field label { color: var(--ink); }
    html[data-theme="dark"] body .qms-record { background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .qms-record-controls a, html[data-theme="dark"] body .qms-record-controls button { color: #a4e1d3; background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .qms-record-controls .qms-delete { color: #ff9a8b; }
    html[data-theme="dark"] body .qms-record-controls select { color: var(--ink); background: var(--field); border-color: var(--line); }
    html[data-theme="dark"] body .qms-revision-form { border-color: var(--line); }
    html[data-theme="dark"] body .qms-revision-form label { color: var(--muted); }
    html[data-theme="dark"] body .qms-revision-form input { color: var(--ink); background: var(--field); border-color: var(--line); }
</style>
<main class="qms-shell">
    <header class="qms-heading">
        <p class="qms-eyebrow">Quality system</p>
        <h1>ISO 9001 quality management support</h1>
        <p>Register controlled documents, monitor risks and actions, and track improvement records.</p>
    </header>

    @if (session('success'))<p class="qms-alert" role="status">{{ session('success') }}</p>@endif
    @if ($errors->any())
        <div class="qms-alert qms-alert-error" role="alert">
            <strong>Check the form entries:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="qms-grid">
        <section class="qms-card">
            <h2>Quality documents</h2><p>Identify current controlled documents and their review dates.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'documents') }}" enctype="multipart/form-data">
                @csrf
                <div class="qms-field"><label for="doc-reference">Reference number</label><input id="doc-reference" name="reference_number" value="{{ old('reference_number') }}" required></div>
                <div class="qms-field"><label for="doc-title">Document title</label><input id="doc-title" name="title" value="{{ old('title') }}" required></div>
                <div class="qms-field"><label for="doc-category">Category</label><input id="doc-category" name="category" value="{{ old('category') }}" required></div>
                <div class="qms-field"><label for="doc-version">Version</label><input id="doc-version" name="version" value="{{ old('version', '1.0') }}" required></div>
                <div class="qms-field"><label for="doc-approval">Approval status</label><select id="doc-approval" name="approval_status" required><option>Draft</option><option>In review</option><option>Approved</option><option>Obsolete</option></select></div>
                <div class="qms-field"><label for="doc-status">Document status</label><select id="doc-status" name="status" required><option>Active</option><option>Obsolete</option></select></div>
                <div class="qms-field"><label for="doc-review">Review date</label><input id="doc-review" name="review_date" type="date"></div>
                <div class="qms-field"><label for="doc-owner">Document owner</label><select id="doc-owner" name="owner_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <div class="qms-field qms-field-wide"><label for="doc-change">Revision note</label><textarea id="doc-change" name="change_note"></textarea></div>
                <div class="qms-field qms-field-wide"><label for="doc-file">Controlled document file</label><input id="doc-file" name="file" type="file" accept=".pdf,.doc,.docx"><span class="settings-hint">Optional. PDF or Word document, up to 10 MB. Files are stored privately.</span></div>
                <button class="qms-submit" type="submit">Add document</button>
            </form>
            <div class="qms-records">
                @forelse ($documents as $row)<div class="qms-record"><strong>{{ $row->reference_number }} · {{ $row->title }}</strong><span>Version {{ $row->version }} · {{ $row->approval_status }} · Review {{ $row->review_date ?: 'not scheduled' }} · {{ $row->approved_at ? 'Approved '.$row->approved_at : 'Not approved' }}</span>@include('qms.record-controls', ['section' => 'documents', 'row' => $row])
                    <form class="qms-revision-form" method="POST" action="{{ route('qms.document.revision.store', $row->id) }}" enctype="multipart/form-data">
                        @csrf
                        <label>New version<input name="version" maxlength="50" required></label>
                        <label>Revision note<input name="change_note" maxlength="2000" required></label>
                        <label>Replacement file<input name="file" type="file" accept=".pdf,.doc,.docx"></label>
                        <button type="submit">Create draft</button>
                    </form>
                </div>
                @empty<p class="qms-empty">No documents registered yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Processes</h2><p>Record process ownership context, inputs and outputs.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'processes') }}">
                @csrf
                <div class="qms-field qms-field-wide"><label for="process-name">Process name</label><input id="process-name" name="name" required></div>
                <div class="qms-field qms-field-wide"><label for="process-description">Description</label><textarea id="process-description" name="description"></textarea></div>
                <div class="qms-field"><label for="process-inputs">Inputs</label><textarea id="process-inputs" name="inputs"></textarea></div>
                <div class="qms-field"><label for="process-outputs">Outputs</label><textarea id="process-outputs" name="outputs"></textarea></div>
                <div class="qms-field"><label for="process-status">Status</label><select id="process-status" name="status" required><option>Active</option><option>Under review</option><option>Inactive</option></select></div>
                <div class="qms-field"><label for="process-owner">Process owner</label><select id="process-owner" name="owner_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <button class="qms-submit" type="submit">Add process</button>
            </form>
            <div class="qms-records">
                @forelse ($processes as $row)<div class="qms-record"><strong>{{ $row->name }}</strong><span>{{ $row->status }}{{ $row->description ? ' · '.$row->description : '' }}</span>@include('qms.record-controls', ['section' => 'processes', 'row' => $row])</div>
                @empty<p class="qms-empty">No processes registered yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Risk register</h2><p>Record identified risks, impact and mitigation status.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'risks') }}">
                @csrf
                <div class="qms-field qms-field-wide"><label for="risk-title">Risk title</label><input id="risk-title" name="title" required></div>
                <div class="qms-field qms-field-wide"><label for="risk-description">Description</label><textarea id="risk-description" name="description" required></textarea></div>
                <div class="qms-field"><label for="risk-level">Risk level</label><select id="risk-level" name="level" required><option>Low</option><option>Medium</option><option>High</option></select></div>
                <div class="qms-field"><label for="risk-status">Status</label><select id="risk-status" name="status" required><option>Open</option><option>Monitoring</option><option>Mitigated</option><option>Closed</option></select></div>
                <div class="qms-field"><label for="risk-impact">Potential impact</label><textarea id="risk-impact" name="impact"></textarea></div>
                <div class="qms-field"><label for="risk-mitigation">Mitigation action</label><textarea id="risk-mitigation" name="mitigation"></textarea></div>
                <div class="qms-field"><label for="risk-owner">Risk owner</label><select id="risk-owner" name="responsible_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <button class="qms-submit" type="submit">Add risk</button>
            </form>
            <div class="qms-records">
                @forelse ($risks as $row)<div class="qms-record"><strong>{{ $row->title }}</strong><span>{{ $row->level }} risk · {{ $row->status }} · {{ $row->description }}</span>@include('qms.record-controls', ['section' => 'risks', 'row' => $row])</div>
                @empty<p class="qms-empty">No risks registered yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Corrective actions</h2><p>Track nonconformities, actions and verification.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'actions') }}">
                @csrf
                <div class="qms-field"><label for="action-reference">Reference number</label><input id="action-reference" name="reference_number" required></div>
                <div class="qms-field"><label for="action-target">Target date</label><input id="action-target" name="target_date" type="date"></div>
                <div class="qms-field qms-field-wide"><label for="action-problem">Nonconformity</label><textarea id="action-problem" name="problem" required></textarea></div>
                <div class="qms-field"><label for="action-cause">Root cause</label><textarea id="action-cause" name="root_cause"></textarea></div>
                <div class="qms-field"><label for="action-action">Corrective action</label><textarea id="action-action" name="action"></textarea></div>
                <div class="qms-field"><label for="action-status">Status</label><select id="action-status" name="status" required><option>Open</option><option>In progress</option><option>Completed</option><option>Verified</option></select></div>
                <div class="qms-field"><label for="action-verification">Verification result</label><textarea id="action-verification" name="verification"></textarea></div>
                <div class="qms-field"><label for="action-owner">Responsible person</label><select id="action-owner" name="responsible_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <button class="qms-submit" type="submit">Add corrective action</button>
            </form>
            <div class="qms-records">
                @forelse ($actions as $row)<div class="qms-record"><strong>{{ $row->reference_number }} · {{ $row->status }}</strong><span>{{ $row->problem }} · Target {{ $row->target_date ?: 'not set' }}</span>@include('qms.record-controls', ['section' => 'actions', 'row' => $row])</div>
                @empty<p class="qms-empty">No corrective actions recorded yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Internal audits</h2><p>Plan audit scope and criteria and record findings.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'audits') }}">
                @csrf
                <div class="qms-field qms-field-wide"><label for="audit-title">Audit title</label><input id="audit-title" name="title" required></div>
                <div class="qms-field"><label for="audit-date">Scheduled date</label><input id="audit-date" name="scheduled_date" type="date"></div>
                <div class="qms-field"><label for="audit-status">Status</label><select id="audit-status" name="status" required><option>Scheduled</option><option>In progress</option><option>Completed</option></select></div>
                <div class="qms-field"><label for="audit-scope">Scope</label><textarea id="audit-scope" name="scope" required></textarea></div>
                <div class="qms-field"><label for="audit-criteria">Criteria</label><textarea id="audit-criteria" name="criteria"></textarea></div>
                <div class="qms-field qms-field-wide"><label for="audit-findings">Findings</label><textarea id="audit-findings" name="findings"></textarea></div>
                <div class="qms-field"><label for="audit-auditor">Auditor</label><select id="audit-auditor" name="auditor_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <button class="qms-submit" type="submit">Add audit</button>
            </form>
            <div class="qms-records">
                @forelse ($audits as $row)<div class="qms-record"><strong>{{ $row->title }}</strong><span>{{ $row->status }} · {{ $row->scheduled_date ?: 'Date not set' }} · {{ $row->scope }}</span>@include('qms.record-controls', ['section' => 'audits', 'row' => $row])</div>
                @empty<p class="qms-empty">No audits scheduled yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Improvement records</h2><p>Capture proposals, actions and effectiveness reviews.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'improvements') }}">
                @csrf
                <div class="qms-field qms-field-wide"><label for="improvement-title">Improvement title</label><input id="improvement-title" name="title" required></div>
                <div class="qms-field qms-field-wide"><label for="improvement-proposal">Proposal</label><textarea id="improvement-proposal" name="proposal" required></textarea></div>
                <div class="qms-field"><label for="improvement-target">Target date</label><input id="improvement-target" name="target_date" type="date"></div>
                <div class="qms-field"><label for="improvement-status">Status</label><select id="improvement-status" name="status" required><option>Proposed</option><option>In progress</option><option>Implemented</option><option>Reviewed</option></select></div>
                <div class="qms-field qms-field-wide"><label for="improvement-effectiveness">Effectiveness review</label><textarea id="improvement-effectiveness" name="effectiveness"></textarea></div>
                <div class="qms-field"><label for="improvement-owner">Responsible person</label><select id="improvement-owner" name="responsible_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <button class="qms-submit" type="submit">Add improvement</button>
            </form>
            <div class="qms-records">
                @forelse ($improvements as $row)<div class="qms-record"><strong>{{ $row->title }}</strong><span>{{ $row->status }} · Target {{ $row->target_date ?: 'not set' }} · {{ $row->proposal }}</span>@include('qms.record-controls', ['section' => 'improvements', 'row' => $row])</div>
                @empty<p class="qms-empty">No improvements recorded yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Quality objectives</h2><p>Set measurable goals, owners, due dates and current progress.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'objectives') }}">
                @csrf
                <div class="qms-field qms-field-wide"><label for="objective-title">Objective</label><input id="objective-title" name="title" required></div>
                <div class="qms-field qms-field-wide"><label for="objective-description">Description and measurement method</label><textarea id="objective-description" name="description"></textarea></div>
                <div class="qms-field"><label for="objective-target">Target value</label><input id="objective-target" name="target_value" type="number" step="any"></div>
                <div class="qms-field"><label for="objective-current">Current value</label><input id="objective-current" name="current_value" type="number" step="any"></div>
                <div class="qms-field"><label for="objective-unit">Unit</label><input id="objective-unit" name="unit" maxlength="50" placeholder="%, days, count"></div>
                <div class="qms-field"><label for="objective-due">Due date</label><input id="objective-due" name="due_date" type="date"></div>
                <div class="qms-field"><label for="objective-owner">Owner</label><select id="objective-owner" name="owner_id"><option value="">Unassigned</option>@foreach ($users as $qmsUser)<option value="{{ $qmsUser->id }}">{{ $qmsUser->name }}</option>@endforeach</select></div>
                <div class="qms-field"><label for="objective-status">Status</label><select id="objective-status" name="status" required><option>Active</option><option>Behind</option><option>Completed</option><option>Cancelled</option></select></div>
                <button class="qms-submit" type="submit">Add objective</button>
            </form>
            <div class="qms-records">
                @forelse ($objectives as $row)<div class="qms-record"><strong>{{ $row->title }}</strong><span>{{ $row->status }} · {{ $row->current_value ?? '—' }} / {{ $row->target_value ?? '—' }} {{ $row->unit }} · Due {{ $row->due_date ?: 'not set' }}</span>@include('qms.record-controls', ['section' => 'objectives', 'row' => $row])</div>
                @empty<p class="qms-empty">No objectives recorded yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Customer feedback</h2><p>Record service feedback and follow-up without storing unnecessary customer identifiers.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'feedback') }}">
                @csrf
                <div class="qms-field"><label for="feedback-reference">Reference number</label><input id="feedback-reference" name="reference_number" required></div>
                <div class="qms-field"><label for="feedback-source">Source</label><input id="feedback-source" name="source" placeholder="Survey, phone, web"></div>
                <div class="qms-field"><label for="feedback-category">Category</label><input id="feedback-category" name="category"></div>
                <div class="qms-field"><label for="feedback-date">Received date</label><input id="feedback-date" name="received_date" type="date"></div>
                <div class="qms-field qms-field-wide"><label for="feedback-description">Feedback</label><textarea id="feedback-description" name="feedback" required></textarea></div>
                <div class="qms-field qms-field-wide"><label for="feedback-response">Response / follow-up</label><textarea id="feedback-response" name="response"></textarea></div>
                <div class="qms-field"><label for="feedback-status">Status</label><select id="feedback-status" name="status" required><option>Open</option><option>In progress</option><option>Resolved</option><option>Closed</option></select></div>
                <button class="qms-submit" type="submit">Add feedback</button>
            </form>
            <div class="qms-records">
                @forelse ($feedback as $row)<div class="qms-record"><strong>{{ $row->reference_number }} · {{ $row->status }}</strong><span>{{ $row->category ?: 'Feedback' }} · {{ $row->received_date ?: 'Date not set' }} · {{ $row->feedback }}</span>@include('qms.record-controls', ['section' => 'feedback', 'row' => $row])</div>
                @empty<p class="qms-empty">No customer feedback recorded yet.</p>@endforelse
            </div>
        </section>

        <section class="qms-card">
            <h2>Management reviews</h2><p>Record review inputs and resulting recommendations for leadership follow-up.</p>
            <form class="qms-form" method="POST" action="{{ route('qms.store', 'reviews') }}">
                @csrf
                <div class="qms-field"><label for="review-date">Review date</label><input id="review-date" name="review_date" type="date" required></div>
                <div class="qms-field"><label for="review-attendees">Attendees</label><input id="review-attendees" name="attendees"></div>
                <div class="qms-field"><label for="review-objectives">Objective results</label><textarea id="review-objectives" name="quality_objectives"></textarea></div>
                <div class="qms-field"><label for="review-audits">Audit results</label><textarea id="review-audits" name="audit_results"></textarea></div>
                <div class="qms-field"><label for="review-feedback">Customer feedback</label><textarea id="review-feedback" name="customer_feedback"></textarea></div>
                <div class="qms-field"><label for="review-process">Process performance</label><textarea id="review-process" name="process_performance"></textarea></div>
                <div class="qms-field"><label for="review-nonconformities">Nonconformities and actions</label><textarea id="review-nonconformities" name="nonconformities"></textarea></div>
                <div class="qms-field"><label for="review-risks">Risks and opportunities</label><textarea id="review-risks" name="risks_opportunities"></textarea></div>
                <div class="qms-field qms-field-wide"><label for="review-recommendations">Recommendations and decisions</label><textarea id="review-recommendations" name="recommendations"></textarea></div>
                <button class="qms-submit" type="submit">Record management review</button>
            </form>
            <div class="qms-records">
                @forelse ($reviews as $row)<div class="qms-record"><strong>Review · {{ $row->review_date }}</strong><span>{{ $row->recommendations ?: 'No recommendations recorded' }}</span>@include('qms.record-controls', ['section' => 'reviews', 'row' => $row])</div>
                @empty<p class="qms-empty">No management reviews recorded yet.</p>@endforelse
            </div>
        </section>
    </div>
</main>
@endsection
