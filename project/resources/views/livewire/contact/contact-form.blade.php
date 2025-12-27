
    <form wire:submit.prevent="submit" class="space-y-4">
        <input type="text" wire:model.defer="name" placeholder="{{ __('Your name') }}" class="input w-full" />

        <input type="email" wire:model.defer="email" placeholder="{{ __('Your email') }}" class="input w-full" />

        <textarea wire:model.defer="message" placeholder="{{ __('Your message') }}" class="textarea w-full"
            rows="4"></textarea>

        <div class="flex justify-end">
            <button type="submit" class="btn-primary">
                {{ __('Send message') }}
            </button>
        </div>
    </form>