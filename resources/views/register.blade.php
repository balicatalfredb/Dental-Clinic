@extends('layout.app')

@section('title', 'Create account - '.($systemBrand['name'] ?? 'Dental'))

@section('content')
<style>
  :root { --navy: #123752; --teal: #087f73; --mint: #dff4ed; --ink: #19324a; --muted: #708296; --line: #e3ebf1; }
  .register-page { box-sizing: border-box; min-height: 100vh; display: grid; grid-template-columns: minmax(300px, .75fr) minmax(0, 1.25fr); color: var(--ink); background: #f5f9fa; font-family: Georgia, 'Times New Roman', serif; }
  .register-aside { display: flex; flex-direction: column; justify-content: space-between; padding: 46px clamp(28px, 6vw, 90px); color: white; background: var(--navy); }
  .register-brand { display: inline-flex; align-items: center; align-self: flex-start; color: white; font-size: 27px; font-weight: 700; text-decoration: none; }
  .register-mark { display: inline-grid; width: 40px; height: 40px; margin-right: 10px; color: var(--navy); background: var(--mint); border-radius: 50%; place-items: center; font: 700 21px Arial, sans-serif; vertical-align: middle; }
  .register-logo { width: 40px; height: 40px; margin-right: 10px; padding: 3px; background: white; border-radius: 50%; object-fit: contain; }
  .register-aside h1 { max-width: 380px; margin: clamp(54px, 15vh, 150px) 0 16px; font-size: clamp(38px, 4vw, 58px); font-weight: 400; line-height: 1.05; letter-spacing: -1.5px; }
  .register-aside p { max-width: 380px; margin: 0 0 10px; color: #b9cbd5; font: 14px/1.7 Arial, sans-serif; }
  .register-form-wrap { display: flex; align-items: center; justify-content: center; padding: 42px clamp(22px, 6vw, 84px); background: #f5f9fa; }
  .register-form { box-sizing: border-box; width: 100%; max-width: 640px; padding: clamp(24px, 4vw, 42px); background: white; border: 1px solid rgba(25, 50, 74, .07); border-radius: 12px; box-shadow: 0 18px 48px rgba(18, 55, 82, .09); }
  .eyebrow { margin: 0 0 9px; color: var(--teal); font: 700 11px Arial, sans-serif; letter-spacing: 1.8px; text-transform: uppercase; }
  .register-form h2 { margin: 0 0 10px; color: var(--ink); font-size: 32px; font-weight: 400; }
  .form-lead { margin: 0 0 25px; color: var(--muted); font: 14px/1.5 Arial, sans-serif; }
  .form-errors { margin: 0 0 18px; padding: 12px 14px 12px 32px; color: #a74435; background: #fff0ed; border-radius: 6px; font: 13px/1.5 Arial, sans-serif; }
  .form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px 14px; }
  .field { min-width: 0; }
  .field label { display: block; margin-bottom: 7px; color: var(--ink); font: 700 12px Arial, sans-serif; }
  .field input { box-sizing: border-box; width: 100%; min-height: 46px; padding: 12px 13px; color: var(--ink); background: #fbfdfd; border: 1px solid var(--line); border-radius: 7px; font: 14px Arial, sans-serif; transition: border-color .15s ease, box-shadow .15s ease; }
  .field input:focus { outline: 0; border-color: var(--teal); box-shadow: 0 0 0 4px rgba(8, 127, 115, .12); }
  .captcha-field { grid-column: 1 / -1; }
  .captcha-instruction { margin-bottom: 10px !important; }
  .captcha-board { position: relative; height: 92px; overflow: hidden; margin-bottom: 12px; border: 1px solid #d5e0e7; border-radius: 8px; background: radial-gradient(circle at 76% 27%, rgba(255,255,255,.8) 0 9px, transparent 10px), linear-gradient(155deg, transparent 0 50%, #8ec49e 51% 100%), linear-gradient(105deg, #85cfe0, #dff4ed); }
  .captcha-target, .captcha-piece { position: absolute; top: 24px; width: 46px; height: 46px; border-radius: 9px; }
  .captcha-target { border: 2px dashed #123752; background: rgba(255,255,255,.38); }
  .captcha-piece { left: 0; border: 2px solid white; background: #087f73; box-shadow: 0 3px 8px rgba(18,55,82,.28); transition: left .06s linear; }
  .captcha-piece::after { position: absolute; top: -8px; left: 13px; width: 14px; height: 14px; content: ''; background: #087f73; border: 2px solid white; border-radius: 50%; }
  .captcha-slider { padding: 0 !important; accent-color: var(--teal); cursor: grab; }
  .captcha-slider:active { cursor: grabbing; }
  .captcha-status { display: block; min-height: 18px; margin-top: 5px; color: var(--muted); font: 12px Arial, sans-serif; }
  .terms { grid-column: 1 / -1; display: flex; align-items: flex-start; gap: 8px; color: var(--muted); font: 12px/1.5 Arial, sans-serif; }
  .terms input { width: 16px; height: 16px; margin: 1px 0 0; accent-color: var(--teal); }
  .terms button { padding: 0; color: var(--teal); background: transparent; border: 0; cursor: pointer; font: inherit; text-decoration: underline; }
  .terms-notice { grid-column: 1 / -1; margin: -8px 0 0; color: var(--teal); font: 12px Arial, sans-serif; }
  .primary-btn { width: 100%; min-height: 48px; margin-top: 21px; padding: 13px 16px; color: white; background: var(--teal); border: 0; border-radius: 7px; box-shadow: 0 5px 12px rgba(8, 127, 115, .18); cursor: pointer; font: 700 13px Arial, sans-serif; transition: background .15s ease, transform .15s ease; }
  .primary-btn:hover { background: #06675e; transform: translateY(-1px); }
  .primary-btn:focus-visible { outline: 3px solid rgba(8, 127, 115, .3); outline-offset: 3px; }
  .form-footer { margin: 22px 0 0; color: var(--muted); text-align: center; font: 13px Arial, sans-serif; }
  .form-footer a { color: var(--teal); font-weight: 700; text-decoration: none; }
  .form-footer a:hover { text-decoration: underline; }
  html[data-theme="dark"] body .register-page { color: #e4edf3; background: #0f2535; }
  html[data-theme="dark"] body .register-form-wrap { background: #0f2535; }
  html[data-theme="dark"] body .register-form { background: #183448; border-color: #3a5263; box-shadow: 0 18px 48px rgba(0, 0, 0, .2); }
  html[data-theme="dark"] body .register-form h2, html[data-theme="dark"] body .field label { color: #e4edf3; }
  html[data-theme="dark"] body .register-aside h1, html[data-theme="dark"] body .register-aside a.register-brand { color: #fff !important; }
  html[data-theme="dark"] body .register-aside p { color: #b9cbd5; }
  html[data-theme="dark"] body .eyebrow { color: #73d5c1; }
  html[data-theme="dark"] body .form-lead, html[data-theme="dark"] body .form-footer { color: #b0c1cc; }
  html[data-theme="dark"] body .form-footer a, html[data-theme="dark"] body .terms button { color: #73d5c1; }
  html[data-theme="dark"] body .field input { color: #e4edf3; background: #102938; border-color: #3a5263; }
  html[data-theme="dark"] body .field input::placeholder { color: #9aafbd; opacity: 1; }
  html[data-theme="dark"] body .terms { color: #b0c1cc; }
  html[data-theme="dark"] body .form-errors { color: #ff9a8b; background: #432d31; }
  html[data-theme="dark"] body .captcha-board { border-color: #3a5263; }
  html[data-theme="dark"] body .captcha-target { border-color: #e4edf3; }
  html[data-theme="dark"] body .captcha-status { color: #b0c1cc; }
  @media (max-width: 800px) {
    .register-page { grid-template-columns: 1fr; }
    .register-aside { padding: 28px 24px; }
    .register-aside h1 { margin: 50px 0 14px; }
    .register-form-wrap { padding: 28px 18px 50px; }
    .register-form { max-width: 620px; }
  }
  @media (max-width: 520px) { .form-grid { grid-template-columns: 1fr; } .terms, .terms-notice { grid-column: auto; } }
</style>

<main class="register-page">
  <aside class="register-aside">
    <a class="register-brand" href="/">
      @if ($systemBrand['logo'])
        <img class="register-logo" src="{{ Storage::url($systemBrand['logo']) }}" alt="">
      @else
        <span class="register-mark">+</span>
      @endif
      {{ $systemBrand['name'] }}
    </a>
    <div><h1>Care starts with a good visit.</h1><p>Create your patient account to book appointments and view your consultation history.</p></div>
  </aside>
  <section class="register-form-wrap">
    <form class="register-form" action="{{ route('register.store') }}" method="POST">
      @csrf
      @if ($errors->any())<ul class="form-errors">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>@endif
      <p class="eyebrow">Get started</p><h2>Create your account</h2><p class="form-lead">Create your patient account to book appointments online and keep track of your visits.</p>
      <div class="form-grid">
        <div class="field"><label for="firstName">First name</label><input type="text" id="firstName" name="firstName" value="{{ old('firstName') }}" placeholder="First name" autocomplete="given-name" required></div>
        <div class="field"><label for="lastName">Last name</label><input type="text" id="lastName" name="lastName" value="{{ old('lastName') }}" placeholder="Last name" autocomplete="family-name" required></div>
        <div class="field"><label for="registerUsername">Username</label><input type="text" id="registerUsername" name="username" value="{{ old('username') }}" placeholder="Choose a username" minlength="3" maxlength="30" pattern="[A-Za-z0-9_\-]+" autocomplete="username" required></div>
        <div class="field"><label for="registerEmail">Email address</label><input type="email" id="registerEmail" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required></div>
        <div class="field"><label for="registerPassword">Password</label><div class="password-field"><input type="password" id="registerPassword" name="password" placeholder="Create a password" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="registerPassword" aria-label="Show password" aria-pressed="false">Show</button></div></div>
        <div class="field"><label for="registerPasswordConfirmation">Confirm password</label><div class="password-field"><input type="password" id="registerPasswordConfirmation" name="password_confirmation" placeholder="Repeat password" minlength="8" autocomplete="new-password" required><button class="password-toggle" type="button" data-password-toggle="registerPasswordConfirmation" aria-label="Show confirm password" aria-pressed="false">Show</button></div></div>
        @if ($captchaEnabled)
          <div class="field captcha-field">
            <label class="captcha-instruction" for="captchaSlider">Slide the puzzle piece into the outlined space</label>
            <div class="captcha-board" id="captchaBoard" data-target="{{ $challenge['target'] }}" aria-hidden="true">
              <span class="captcha-target" id="captchaTarget"></span>
              <span class="captcha-piece" id="captchaPiece"></span>
            </div>
            <input class="captcha-slider" id="captchaSlider" name="captcha_answer" type="range" min="0" max="100" step="1" value="0" aria-describedby="captchaStatus" required>
            <span class="captcha-status" id="captchaStatus" role="status" aria-live="polite">Use the slider or arrow keys to complete the puzzle.</span>
          </div>
        @endif
        <label class="terms"><input type="checkbox" name="terms" required><span>I agree to the <button type="button" onclick="showTerms()">terms and conditions</button>.</span></label>
        <p id="termsNotice" class="terms-notice" role="status" aria-live="polite"></p>
      </div>
      <button class="primary-btn" type="submit">Create patient account</button>
      <p class="form-footer">Already have an account? <a href="/">Sign in</a></p>
    </form>
  </section>
</main>

<script>
  function showTerms() { document.getElementById('termsNotice').textContent = 'Terms and conditions will be available soon.'; }

  (() => {
    const board = document.getElementById('captchaBoard');
    const slider = document.getElementById('captchaSlider');
    if (!board || !slider) return;

    const piece = document.getElementById('captchaPiece');
    const target = document.getElementById('captchaTarget');
    const status = document.getElementById('captchaStatus');
    const targetValue = Number(board.dataset.target);

    const positionPuzzle = () => {
      const travelDistance = Math.max(0, board.clientWidth - piece.offsetWidth);
      piece.style.left = `${travelDistance * Number(slider.value) / 100}px`;
      target.style.left = `${travelDistance * targetValue / 100}px`;
      status.textContent = Number(slider.value) === targetValue
        ? 'Puzzle complete. You can create your account.'
        : 'Use the slider or arrow keys to complete the puzzle.';
    };

    slider.addEventListener('input', positionPuzzle);
    window.addEventListener('resize', positionPuzzle);
    positionPuzzle();
  })();
</script>
@endsection
