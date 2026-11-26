<nav class="site-nav" aria-label="Main navigation">
    <a class="site-brand" href="{{ route('dashboard') }}">
        @if ($systemBrand['logo'])
            <img class="site-brand-logo" src="{{ Storage::url($systemBrand['logo']) }}" alt="">
        @else
            <span class="site-brand-mark" aria-hidden="true">+</span>
        @endif
        <span>{{ $systemBrand['name'] }}</span>
    </a>
    <ul class="site-nav-links">
        <li><a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>Dashboard</a></li>
        @canany(['book-appointments', 'manage-appointments', 'view-clinician-appointments'])
            <li><a href="{{ route('appointments.index') }}" @if(request()->routeIs('appointments.*')) aria-current="page" @endif>Appointments</a></li>
        @endcanany
        @can('manage-users')
            <li><a href="{{ route('users.index') }}" @if(request()->routeIs('users.*')) aria-current="page" @endif>User Management</a></li>
            <li><a href="{{ route('roles.index') }}" @if(request()->routeIs('roles.*')) aria-current="page" @endif>Roles &amp; permissions</a></li>
        @endcan
        @can('view-audit')
            <li><a href="{{ route('audit.index') }}" @if(request()->routeIs('audit.*')) aria-current="page" @endif>Audit Trail</a></li>
        @endcan
        @can('manage-backups')
            <li><a href="{{ route('backups.index') }}" @if(request()->routeIs('backups.*')) aria-current="page" @endif>Backups</a></li>
        @endcan
        @can('view-reports')
            <li><a href="{{ route('reports.index') }}" @if(request()->routeIs('reports.*')) aria-current="page" @endif>Reports</a></li>
        @endcan
        @can('manage-settings')
            <li><a href="{{ route('settings.index') }}" @if(request()->routeIs('settings.*')) aria-current="page" @endif>Settings</a></li>
        @endcan
        @can('manage-qms')
            <li><a href="{{ route('qms.index') }}" @if(request()->routeIs('qms.*')) aria-current="page" @endif>QMS</a></li>
        @endcan
        <li><a href="{{ route('profile') }}" @if(request()->routeIs('profile*', 'two-factor.*')) aria-current="page" @endif>User Profile</a></li>
    </ul>
    @php($philippineNow = now('Asia/Manila'))
    <div class="site-nav-account">
        <span class="site-nav-greeting">Hello, {{ Str::before(auth()->user()->name, ' ') }}</span>
        <time class="site-nav-time" id="philippineDateTime" datetime="{{ $philippineNow->toIso8601String() }}">
            {{ $philippineNow->format('D, M j, Y') }} - {{ $philippineNow->format('g:i:s A') }} PHT
        </time>
    </div>
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit">Log out</button>
    </form>
</nav>
<script>
    (() => {
        const dateTime = document.getElementById('philippineDateTime');
        if (!dateTime) return;

        const dateFormatter = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
        const timeFormatter = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            hour: 'numeric',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
        });
        const updateDateTime = () => {
            const now = new Date();
            dateTime.dateTime = now.toISOString();
            dateTime.textContent = `${dateFormatter.format(now)} - ${timeFormatter.format(now)} PHT`;
        };

        updateDateTime();
        window.setInterval(updateDateTime, 1000);
    })();
</script>
