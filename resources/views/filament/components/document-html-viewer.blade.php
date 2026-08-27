@props(['htmlContent'])

<style>
    /* Anchor links styling */
    .pdf-content a {
        color: #0066cc !important;
        text-decoration: underline !important;
        cursor: pointer !important;
    }

    .pdf-content a:hover {
        color: #0052a3 !important;
    }
</style>

@if($htmlContent)
    <div class="prose prose-sm dark:prose-invert max-w-none pdf-content" id="document-viewer">
        {!! $htmlContent !!}
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Open PDF links in new tab
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
@else
    <p class="text-gray-500 italic">Document HTML not available. Please re-upload the document.</p>
@endif
