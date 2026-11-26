@extends('layouts.guest')
@section('title', 'Create account · Shell')
@section('content')
<div class="auth-shell">
    <a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a>
    <div class="auth-card auth-card-international">
        <span class="eyebrow">GLOBAL CLIENT REGISTRATION</span>
        <h1>Create your account</h1>
        <p class="muted">Register from your country and use your international phone number.</p>
        @if(session('referral_error'))<div class="alert alert-error">{{ session('referral_error') }}</div>@endif
        @if(session('referral_code'))<div class="notice-banner"><div><strong>You were invited to SHELL</strong><p>Your registration will be linked to the person who shared this referral invitation.</p></div></div>@endif
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
                <div class="phone-input-wrap"><span class="phone-prefix" id="phone-prefix">+250</span><input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel-national" inputmode="tel" placeholder="788 000 000" maxlength="25" aria-describedby="phone-help"></div>
                <small class="field-hint" id="phone-help">Enter your phone number without the country calling code.</small>
            </div>
            <label>Password<input type="password" name="password" required autocomplete="new-password"></label>
            <label>Confirm password<input type="password" name="password_confirmation" required autocomplete="new-password"></label>
            <button class="button button-primary button-full" type="submit">Create client account</button>
        </form>
        <div class="auth-bottom">Already registered? <a href="{{ route('login') }}">Sign in</a></div>
    </div>
    <p class="auth-disclaimer">Country and phone details help prepare your account for international services. Phone ownership is not verified by this registration step.</p>
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
