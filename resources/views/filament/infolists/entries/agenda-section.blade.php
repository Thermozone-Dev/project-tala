@if($document)
    <div id="document-viewer-agenda" class="pt-3">
        @include('livewire.document-highlights-modal', ['htmlContent' => $htmlContent])
    </div>
@else
    <p class="text-sm text-gray-500 dark:text-gray-400">No agenda has been uploaded for this meeting yet.</p>
@endif
