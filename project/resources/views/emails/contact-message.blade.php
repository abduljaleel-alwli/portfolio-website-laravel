<h2>{{ __('New contact message') }}</h2>

<p><strong>{{ __('Name') }}:</strong> {{ $contact->name }}</p>
<p><strong>{{ __('Email') }}:</strong> {{ $contact->email }}</p>
<p><strong>{{ __('Phone') }}:</strong> {{ $contact->phone }}</p>
<p><strong>{{ __('IP address') }}:</strong> {{ $contact->ip_address ?? '—' }}</p>

<p><strong>{{ __('Message') }}:</strong></p>
<p>{{ $contact->message }}</p>
