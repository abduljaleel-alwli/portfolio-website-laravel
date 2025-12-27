<h2>{{ __('New contact message') }}</h2>

<p><strong>{{ __('Name') }}:</strong> {{ $message->name }}</p>
<p><strong>{{ __('Email') }}:</strong> {{ $message->email }}</p>
<p><strong>{{ __('IP address') }}:</strong> {{ $message->ip_address ?? '—' }}</p>

<hr>

<p>{{ $message->message }}</p>
