@extends('layouts.guest')
@section('title', 'Create account · Shell')
@section('content')
<div class="auth-shell">
    <a class="brand auth-brand" href="{{ route('home') }}"><span class="brand-mark">S</span><span>SHELL<span class="brand-sub">FINANCIAL TERMINAL</span></span></a>
    <div class="auth-card auth-card-international">
        <span class="eyebrow">GLOBAL CLIENT REGISTRATION</span>
        <h1>Create your account</h1>
        <p class="muted">Register from your country and use your international phone number.</p>
        @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('register') }}" class="form-stack" id="international-registration">
            @csrf
            <label>Full name<input type="text" name="name" value="{{ old('name') }}" required autocomplete="name" maxlength="255"></label>
            <label>Email address<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" maxlength="255"></label>
            <div class="international-field">
                <label for="country_code">Country / region</label>
                <select id="country_code" name="country_code" required autocomplete="country">
                    @foreach(config('phone-countries', []) as $code => $dial)
                        <option value="{{ $code }}" data-dial="{{ $dial }}" @selected(old('country_code', 'RW') === $code)>{{ $code }} ({{ $dial }})</option>
                    @endforeach
                </select>
                <small class="field-hint">Choose the country where your phone number is registered.</small>
            </div>
            <div class="international-field">
                <label for="phone_number">Mobile phone number</label>
                <div class="phone-input-wrap"><span class="phone-prefix" id="phone-prefix">+250</span><input id="phone_number" type="tel" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel-national" inputmode="tel" placeholder="788 000 000" maxlength="25" aria-describedby="phone-help"></div>
                <small class="field-hint" id="phone-help">Enter the national number without the country calling code. We save it in international format.</small>
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
