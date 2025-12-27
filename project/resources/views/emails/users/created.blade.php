<h2>{{ __('Welcome to :app', ['app' => config('app.name')]) }}</h2>

<p>{{ __('Your account has been created successfully.') }}</p>

<p><strong>{{ __('Login URL') }}:</strong>
    <a href="{{ route('login') }}">{{ route('login') }}</a>
</p>

<p><strong>{{ __('Email') }}:</strong> {{ $user->email }}</p>
<p><strong>{{ __('Password') }}:</strong> {{ $plainPassword }}</p>

<hr>

<p>{{ __('Please change your password after first login.') }}</p>
