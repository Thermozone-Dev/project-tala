<x-filament-panels::page>

    {{$this->infolist}}

    <div class="mt-6 shadow" style='height: 80vh; position: relative;'>

        <div id="pdf-loader" class="absolute inset-0 flex items-center justify-center z-10">
            <x-filament::loading-indicator class="h-5 w-5" />
            <p class=" text-sm text-gray-600 ml-2">Loading PDF...</p>
        </div>

        <iframe
            src="{{ route('preview-report', ['report' => $this->record->id, 'type' => 'pdf']) }}"
            style="height: 100%; width: 100%;"
            onload="document.getElementById('pdf-loader').style.display='none'"
        ></iframe>

    </div>

</x-filament-panels::page>
