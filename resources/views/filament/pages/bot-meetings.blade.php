<x-filament-panels::page>

    <!-- Tabs Content -->
    <x-filament::section>
        <x-filament::tabs class="flex justify-center ">
            <x-filament::tabs.item
                wire:click.debounce.200ms="switchTab('meet')"
                :active="$activeTab === 'meet'"
                class="flex-1">
                Meetings
            </x-filament::tabs.item>

            <x-filament::tabs.item
                wire:click.debounce.200ms="switchTab('members')"
                :active="$activeTab === 'members'"
                class="flex-1">
                Members
            </x-filament::tabs.item>
        </x-filament::tabs>

        <!-- Tab Content -->
        <div class="mt-1">
            @if ($activeTab == 'meet')
                @livewire(\App\Livewire\BotMeetingsTable::class)
            @else
                @livewire(\App\Livewire\BotMembersTable::class)
            @endif
        </div>
    </x-filament::section>
</x-filament-panels::page>
