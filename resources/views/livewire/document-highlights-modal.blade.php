<div class="space-y-6">
    <!-- Document Content Preview -->
    @if($htmlContent)
        <div class="prose prose-sm dark:prose-invert max-w-none pdf-content border rounded p-3 bg-gray-50 dark:bg-gray-800 max-h-96 overflow-y-auto" id="document-viewer">
            {!! $htmlContent !!}
        </div>
    @else
        <p class="text-gray-500 italic text-sm">Document preview not available.</p>
    @endif
</div>

<style>
    .pdf-content a {
        color: #0066cc !important;
        text-decoration: underline !important;
        cursor: pointer !important;
    }

    .pdf-content a:hover {
        color: #0052a3 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const viewer = document.getElementById('document-viewer');
        if (viewer) {
            viewer.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    if (href && href.includes('/documents/')) {
                        window.open(href, '_blank', 'noopener,noreferrer');
                    }
                });
            });
        }
    });
</script>
