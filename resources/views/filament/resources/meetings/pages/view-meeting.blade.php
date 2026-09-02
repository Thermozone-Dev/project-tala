@php
    $latestDocument = $this->getLatestDocument();
    $latestDocumentContent = $this->getLatestDocumentContent();
@endphp

<div>
    {{ $this->infolistData }}

    @if ($latestDocument)
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                Latest Agenda: {{ $latestDocument->title ?? $latestDocument->original_filename }}
            </h2>
            @include('livewire.document-highlights-modal', ['document' => $latestDocument, 'htmlContent' => $latestDocumentContent])
        </div>
    @else
        <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-900">
            <p class="text-gray-500 dark:text-gray-400">No agenda has been uploaded for this meeting yet.</p>
        </div>
    @endif

    @if (!$this->isExecutive())
        <!-- Agenda hidden for non-executive users -->
    @endif
</div>
