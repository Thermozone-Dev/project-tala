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
    .pdf-content {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        line-height: 1.6;
        color: #333;
        font-size: 14px;
    }

    .pdf-content a {
        color: #0066cc !important;
        text-decoration: underline !important;
        cursor: pointer !important;
    }

    .pdf-content a:hover {
        color: #0052a3 !important;
    }

    /* List styling */
    .pdf-content ul, .pdf-content ol {
        margin: 15px 0 !important;
        padding-left: 40px !important;
    }

    .pdf-content li {
        margin: 8px 0 !important;
    }

    /* Heading styling */
    .pdf-content h1, .pdf-content h2, .pdf-content h3,
    .pdf-content h4, .pdf-content h5, .pdf-content h6 {
        margin-top: 15px !important;
        margin-bottom: 10px !important;
        font-weight: bold !important;
    }

    .pdf-content h1 { font-size: 28px !important; }
    .pdf-content h2 { font-size: 24px !important; }
    .pdf-content h3 { font-size: 20px !important; }
    .pdf-content h4 { font-size: 18px !important; }
    .pdf-content h5 { font-size: 16px !important; }
    .pdf-content h6 { font-size: 14px !important; }

    /* Table styling */
    .pdf-content table {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 15px 0 !important;
    }

    .pdf-content table, .pdf-content th, .pdf-content td {
        border: 1px solid #999 !important;
    }

    .pdf-content th {
        background-color: #f2f2f2 !important;
        padding: 12px !important;
        text-align: left !important;
        font-weight: bold !important;
    }

    .pdf-content td {
        padding: 12px !important;
        vertical-align: top !important;
    }

    /* Paragraph styling */
    .pdf-content p {
        margin: 10px 0 !important;
    }

    /* Image styling */
    .pdf-content div[style*="text-align: center"] img {
        max-width: 100% !important;
        height: auto !important;
        display: block !important;
        margin: 0 auto !important;
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
