@php($initials = collect(explode(' ', $user->name))->map(fn ($part) => substr($part, 0, 1))->join(''))
<tr data-user-row="{{ $user->id }}">
    <td><div class="user-cell">@if ($user->profile_picture)<img class="avatar" style="object-fit:cover" src="{{ Storage::url($user->profile_picture) }}" alt="{{ $user->name }}">@else<span class="avatar">{{ strtoupper(substr($initials, 0, 2)) }}</span>@endif<div><span class="user-name">{{ $user->name }}</span><span class="subtext">{{ '@' . $user->username }} · {{ $user->department }}</span></div></div></td>
    <td><span class="role">{{ $user->role }}</span></td>
    <td><span class="subtext">{{ $user->email }}</span><br><span class="subtext">{{ $user->phone ?: 'No phone provided' }}</span></td>
    <td><span class="subtext">{{ $user->created_at->format('M d, Y') }}</span></td>
    <td><span class="status" data-status="{{ $user->status }}">{{ $user->status }}</span></td>
    <td>
        <button class="actions js-edit-user" type="button" data-url="{{ route('users.update', $user) }}" data-name="{{ $user->name }}" data-department="{{ $user->department }}" data-email="{{ $user->email }}" data-phone="{{ $user->phone }}" data-role="{{ $user->role }}" data-status="{{ $user->status }}">Edit</button>
        <form action="{{ route('users.destroy', $user) }}" method="POST" class="js-delete-user" style="display:inline">@csrf @method('DELETE')<button class="actions delete-action" type="submit">Delete</button></form>
    </td>
</tr>