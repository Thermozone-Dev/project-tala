<x-filament-panels::page>

    {{$this->form}}

    @can('View:ActivityLog')
        <div class="mt-6">
            {{ $this->table }}
        </div>
    @endcan
</x-filament-panels::page>
