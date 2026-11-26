@include('users.form', [
    'title' => 'Add team member',
    'description' => 'Create a new practice team profile.',
    'action' => route('users.store'),
    'method' => 'POST',
    'submitLabel' => 'Add member',
])