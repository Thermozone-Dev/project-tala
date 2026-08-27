<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">{{ $document->original_filename }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Uploaded {{ $document->created_at->diffForHumans() }}
                </p>
            </div>
            <a href="javascript:history.back()"
               class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                Back
            </a>
        </div>

        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">
                Attached PDFs
                <span class="ml-2 px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-sm">
                    {{ $document->highlights()->count() }}
                </span>
            </h3>

            @if($document->highlights()->count())
                <div class="overflow-x-auto">
                    {{ $this->table }}
                </div>
            @else
                <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-8 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="text-gray-600 dark:text-gray-400 text-lg font-medium">No attachments yet</p>
                    <p class="text-gray-500 dark:text-gray-500 text-sm mt-1">Highlight text in the document to attach PDFs</p>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
