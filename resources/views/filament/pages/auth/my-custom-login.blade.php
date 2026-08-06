<x-filament-panels::page.simple>
    <form wire:submit="authenticate" class="space-y-6">
        {{ $this->form }}

        <x-filament::button
            type="submit"
            class="w-full"
            wire:loading.attr="disabled"
            wire:target="authenticate"
        >
            <span wire:loading.remove wire:target="authenticate">
                Submit
            </span>

        </x-filament::button>
    </form>

    <x-filament::modal
        id="data-privacy-modal"
        icon="heroicon-o-shield-check"
        icon-color="primary"
        width="xl"
    >
        <x-slot name="heading">
            Notice of Confidentiality
        </x-slot>

        <div class="space-y-6">
            <div>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    The use of this system is restricted to official users who have been granted access and authorization by AFPSLAI. Your use of this system constitutes your express consent to AFPSLAI's policies, monitoring, recording and auditing of all system activities. Any actual or attempted unauthorized access or use of this system is strictly prohibited. Unauthorized disclosure or use of the information and contents of the System may be subject to AFPSLAI's disciplinary action and/or legal action.
                </p>
            </div>
        </div>

        <x-filament::button
            size="sm"
            class="w-full"
            wire:click="agreeToTerms"
            wire:loading.attr="disabled"
            wire:target="agreeToTerms"
        >
            <span wire:loading.remove wire:target="agreeToTerms">
                Agree
            </span>
        </x-filament::button>

    </x-filament::modal>

</x-filament-panels::page.simple>
