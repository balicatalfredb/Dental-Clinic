@include('users.form', [
    'title' => 'Edit team member',
    'description' => 'Update this team member\'s details.',
    'action' => route('users.update', $user),
    'method' => 'PUT',
    'submitLabel' => 'Save changes',
    'user' => $user,
])