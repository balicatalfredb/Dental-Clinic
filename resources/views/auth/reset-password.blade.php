@extends('layout.app')
@section('title', 'Choose a new password - '.($systemBrand['name'] ?? 'Dental'))
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
    <h1>Choose a new password</h1>
    <p class="recovery-lead">Create a new password for your account. Use at least 8 characters with uppercase, lowercase, a number, and a symbol.</p>
    @if ($errors->any())<ul class="recovery-errors" role="alert">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
    <form action="{{ route('password.update') }}" method="POST">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <div class="recovery-field">
        <label for="email">Email address</label>
        <input id="email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" required>
      </div>
      <div class="recovery-field">
        <label for="password">New password</label>
        <div class="password-field">
          <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
          <button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show new password" aria-pressed="false">Show</button>
        </div>
      </div>
      <div class="recovery-field">
        <label for="password_confirmation">Confirm new password</label>
        <div class="password-field">
          <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
          <button class="password-toggle" type="button" data-password-toggle="password_confirmation" aria-label="Show confirm new password" aria-pressed="false">Show</button>
        </div>
      </div>
      <button class="recovery-submit" type="submit">Reset password</button>
    </form>
    <a class="recovery-back" href="{{ route('login') }}">Back to sign in</a>
  </section>
</main>
@endsection
