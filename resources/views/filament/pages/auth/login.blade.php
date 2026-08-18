@php
    use Filament\Facades\Filament;
@endphp

<x-filament-panels::page.simple>
    <div x-data="{ privacyOpen: false }">
        <form wire:submit="authenticate" class="space-y-6">
            {{ $this->form }}

            <!-- Terms Checkbox (above Remember Me) -->
            <div class="space-y-2">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" name="terms_accepted" value="1" required class="mt-1 rounded border-gray-300">
                    <span class="text-sm leading-relaxed">
                        I agree to the Terms of Service and
                        <button type="button" @click="privacyOpen = true" class="text-primary-600 hover:text-primary-800 underline font-semibold">
                            Privacy Policy
                        </button>
                    </span>
                </label>
                @error('terms_accepted')
                    <p class="text-sm text-danger-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <x-filament::button type="submit" class="w-full">
                {{ __('filament-panels::pages/auth/login.form.actions.authenticate.label') }}
            </x-filament::button>
        </form>

        <!-- Privacy Policy Modal -->
        <div x-show="privacyOpen" x-transition class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="privacyOpen = false">
            <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-y-auto p-6">
                <div class="flex justify-between items-start mb-6">
                    <h2 class="text-2xl font-bold">Privacy Policy</h2>
                    <button type="button" @click="privacyOpen = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    @include('auth.privacy-policy')
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page.simple>
