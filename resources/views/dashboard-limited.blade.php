@extends('layout.app')

@section('title', 'Staff Dashboard')

@section('content')
<style>
    body { margin: 0; color: #19324a; background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
    .staff-dashboard { max-width: 960px; margin: 0 auto; padding: 50px 5vw 70px; }
    .staff-eyebrow { margin: 0 0 9px; color: #087f73; font: 700 11px Arial, sans-serif; letter-spacing: 1.8px; text-transform: uppercase; }
    .staff-dashboard h1 { margin: 0 0 12px; font-size: clamp(34px, 4vw, 52px); font-weight: 400; }
    .staff-lead { margin: 0 0 28px; color: #708296; font: 14px/1.5 Arial, sans-serif; }
    .staff-panel { padding: 26px; background: white; border: 1px solid #e3ebf1; }
    .staff-panel h2 { margin: 0 0 10px; font-size: 23px; font-weight: 400; }
    .staff-panel p { margin: 0 0 18px; color: #708296; font: 14px/1.5 Arial, sans-serif; }
    .staff-link { display: inline-block; padding: 11px 15px; color: white; background: #087f73; border-radius: 4px; font: 700 12px Arial, sans-serif; text-decoration: none; }
    html[data-theme="dark"] body .staff-panel { color: var(--ink); background: var(--surface); border-color: var(--line); }
    html[data-theme="dark"] body .staff-lead, html[data-theme="dark"] body .staff-panel p { color: var(--muted); }
    @media (max-width: 760px) { .staff-dashboard { padding: 35px 20px 50px; } }
</style>

@include('layout.navigation')

<main class="staff-dashboard">
    <p class="staff-eyebrow">Staff workspace</p>
    <h1>Welcome, {{ Str::before($user->name, ' ') }}.</h1>
    <p class="staff-lead">Your dashboard shows the tools available for your account.</p>
    <section class="staff-panel">
        <h2>Your account</h2>
        <p>Review or update your profile and account security settings.</p>
        <a class="staff-link" href="{{ route('profile') }}">Manage your profile</a>
    </section>
</main>
@endsection
