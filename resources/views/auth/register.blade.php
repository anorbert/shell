@extends('layouts.guest')
@section('title', 'Create account · Shell')
@section('content')
<div class="auth-shell">
    <a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a>
    <div class="auth-card auth-card-international">
        <h1>Create your account</h1>
        @if(session('referral_error'))<div class="alert alert-error">{{ session('referral_error') }}</div>@endif
        @if(session('referral_code'))<div class="notice-banner"><div><strong>Referral invitation detected</strong><p>The referral code from your invitation link has been added below. You can change it or clear it before registering.</p></div></div>@endif
        @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
        <a class="button button-google button-full" href="{{ route('google.redirect') }}"><svg aria-hidden="true" viewBox="0 0 48 48" width="18" height="18"><path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5Z"/><path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.74 7.18l7.72 5.99c4.5-4.16 7.06-10.28 7.06-17.64Z"/><path fill="#FBBC05" d="M10.53 28.59A14.4 14.4 0 0 1 9.75 24c0-1.59.27-3.13.76-4.59l-7.98-6.19A23.9 23.9 0 0 0 0 24c0 3.88.93 7.55 2.56 10.78l7.97-6.19Z"/><path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.9-5.81l-7.72-5.99c-2.14 1.45-4.89 2.3-8.18 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48Z"/></svg>Sign up with Google</a>
        <div class="auth-divider"><span>OR REGISTER WITH EMAIL</span></div>
        <form method="POST" action="{{ route('register') }}" class="form-stack" id="international-registration">
            @csrf
            <label>Full name<input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" maxlength="255"></label>
            <label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" maxlength="255"></label>
            <div class="international-field">
                <label for="country_code">Country</label>
                <select id="country_code" name="country_code" required autocomplete="country">
                    @foreach(config('phone-countries', []) as $code => $dial)
                        <option value="{{ $code }}" data-dial="{{ $dial }}" @selected(old('country_code', 'US') === $code)>{{ $code }} ({{ $dial }})</option>
                    @endforeach
                </select>
                <small class="field-hint">Select your country calling code.</small>
            </div>
            <div class="international-field">
                <label for="phone_number">Mobile phone number</label>
                <div class="phone-input-wrap"><span class="phone-prefix" id="phone-prefix">{{ config('phone-countries')[old('country_code', 'US')] ?? '+1' }}</span><input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel-national" inputmode="tel" placeholder="202 555 0100" maxlength="25" aria-describedby="phone-help"></div>
                <small class="field-hint" id="phone-help">Enter your phone number without the country calling code.</small>
            </div>
            <div class="international-field">
                <label for="referral_code">Referral code <span class="field-hint" style="display:inline;">(optional)</span></label>
                <input id="referral_code" type="text" name="referral_code" value="{{ old('referral_code', session('referral_code')) }}" maxlength="20" autocomplete="off" autocapitalize="characters" spellcheck="false" placeholder="Enter referral code">
                <small class="field-hint">Have a referral code? Enter it here. Leave blank if you were not referred.</small>
                @error('referral_code')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <label>Password<span class="auth-password-wrap"><input id="register-password" type="password" name="password" required autocomplete="new-password"><button class="auth-password-toggle" type="button" data-password-toggle="register-password" aria-controls="register-password" aria-label="Show password" aria-pressed="false"><svg class="auth-password-icon auth-password-icon-show" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="auth-password-icon auth-password-icon-hide" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.3 0 2.5-.3 3.6-.8"/></svg></button></span></label>
            <label>Confirm password<span class="auth-password-wrap"><input id="register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"><button class="auth-password-toggle" type="button" data-password-toggle="register-password-confirmation" aria-controls="register-password-confirmation" aria-label="Show password" aria-pressed="false"><svg class="auth-password-icon auth-password-icon-show" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="auth-password-icon auth-password-icon-hide" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 18 18"/><path d="M10.6 10.6a2 2 0 0 0 2.8 2.8"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.1 3.8"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.3 0 2.5-.3 3.6-.8"/></svg></button></span></label>
            <button class="button button-primary button-full" type="submit">Create client account</button>
        </form>
        <div class="auth-bottom">Already registered? <a href="{{ route('login') }}">Sign in</a></div>
    </div>
</div>
@endsection
@push('scripts')
<script>
(() => {
    const select = document.getElementById('country_code');
    const prefix = document.getElementById('phone-prefix');
    if (!select || !prefix) return;
    const names = typeof Intl !== 'undefined' && Intl.DisplayNames
        ? new Intl.DisplayNames([document.documentElement.lang || navigator.language || 'en'], { type: 'region' })
        : null;
    const options = [...select.options];
    options.forEach(option => {
        const name = names?.of(option.value) || option.value;
        option.textContent = name + ' (' + option.dataset.dial + ')';
    });
    options.sort((a, b) => a.textContent.localeCompare(b.textContent, navigator.language || 'en'));
    options.forEach(option => select.appendChild(option));
    const updatePrefix = () => {
        prefix.textContent = select.selectedOptions[0]?.dataset.dial || '';
    };
    select.addEventListener('change', updatePrefix);
    updatePrefix();
})();
</script>
@endpush
