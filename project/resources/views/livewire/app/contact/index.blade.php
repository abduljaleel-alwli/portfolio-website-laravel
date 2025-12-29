<?php

use Livewire\Volt\Component;

new class extends Component {

};
?>


<div class="max-w-xl mx-auto space-y-6">
    <h1 class="text-3xl font-bold">
        {{ __('Contact us') }}
    </h1>

    <section class="space-y-6 max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold">{{ settings('contact.title') }}</h1>

        <p class="text-gray-700">
            {{ settings('contact.description') }}
        </p>

        @if (settings('contact.phone'))
            <p><strong>{{ __('Phone') }}:</strong> {{ settings('contact.phone') }}</p>
        @endif

        @if (settings('contact.map_url'))
            <iframe src="{{ settings('contact.map_url') }}" class="w-full h-64 border rounded" loading="lazy"></iframe>
        @endif

        <a
            href="https://wa.me/{{ settings('contact.phone') }}"
            target="_blank"
            data-analytics
            data-event="whatsapp_click"
            data-entity="whatsapp"
            data-id="whatsapp"
            data-source="contact_page"
            class="btn-whatsapp"
        >
            WhatsApp
        </a>

        <div class="flex gap-4 items-center">
            @foreach ((array) settings('contact.social_links', []) as $social)
                <a href="{{ $social['url'] }}" 
                    target="_blank"
                    data-analytics
                    data-event="social_click"
                    data-entity="social"
                    data-id="{{ $social['platform'] }}"
                    data-source="contact_page"
                    class="flex items-center gap-2 text-gray-700 hover:text-blue-600"
                    aria-label="{{ $social['platform'] }}">
                    @if (($social['icon_type'] ?? null) === 'class')
                        <i class="{{ $social['icon_value'] }} text-xl"></i>
                    @elseif (($social['icon_type'] ?? null) === 'svg')
                        <span class="w-5 h-5 inline-block">
                            {!! $social['icon_value'] !!}
                        </span>
                    @endif

                    <span class="text-sm font-medium">
                        {{ ucfirst($social['platform']) }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Contact Form --}}
        @livewire('app.contact.contact-form')
    </section>
</div>