@extends('layout.app')
@section('title', 'Forgot password - '.($systemBrand['name'] ?? 'Dental'))
@section('content')
<main class="recovery-page">
  <section class="recovery-card">
    <a class="recovery-brand" href="{{ route('login') }}">
      @if ($systemBrand['logo'])
        <img class="recovery-logo" src="{{ Storage::url($systemBrand['logo']) }}" alt="">
      @else
        <span class="recovery-mark">+</span>
      @endif
      {{ $systemBrand['name'] }}
    </a>
    <p class="recovery-eyebrow">Account recovery</p>
    <h1>Reset your password</h1>
    <p class="recovery-lead">Enter the email address linked to your account. If it matches, we’ll send you a secure reset link.</p>
    @if (session('status'))<p class="recovery-status" role="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<ul class="recovery-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <form action="{{ route('password.email') }}" method="POST">
      @csrf
      <div class="recovery-field">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" placeholder="you@example.com" required>
      </div>
      <button class="recovery-submit" type="submit">Send reset link</button>
    </form>
    <a class="recovery-back" href="{{ route('login') }}">Back to sign in</a>
  </section>
</main>
@endsection
