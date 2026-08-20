@php
    use Filament\Support\Enums\MaxWidth;
@endphp

<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-900 rounded-lg shadow p-6">
            <div id="editor-container" class="mt-4 mb-4 border border-gray-200 rounded;">
                <textarea id="tinymce-editor"
                          data-gramm="false"
                          data-gramm_editor="false"
                          style="visibility: visible;"></textarea>
            </div>

            <script>
                // Store document content in a global variable (before TinyMCE initializes)
                window.documentContent = {!! json_encode($this->getDocumentContent() ?? '') !!};
            </script>

            <div class="mt-6 flex gap-3 justify-end">
                <a href="{{ $this->meeting->id }}/manage-documents"
                   class="inline-flex items-center justify-center px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </a>
                <button type="button" id="save-document-btn" onclick="saveTinyMceContent(); return false;"
                        class="inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 cursor-pointer">
                    Save Document
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
        <!-- Self-hosted TinyMCE (free, no API key needed) -->
        <script src="{{ asset('vendor/tinymce/tinymce.min.js') }}"></script>
        <script>
            tinymce.init({
                selector: '#tinymce-editor',
                height: 500,
                menubar: false,
                branding: false,
                // GPL License for self-hosted TinyMCE 7
                license_key: 'gpl',
                // Tell TinyMCE where to find its resources
                base_url: '{{ asset("vendor/tinymce") }}',
                // Disable Grammarly interference
                browser_spellcheck: false,
                // TinyMCE 7: Use only essential plugins that are available
                plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen table help wordcount',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | code | highlightText attachPdf',
                // Security: Block dangerous elements
                invalid_elements: 'script,iframe,embed,object,style,input,form,button',
                extended_valid_elements: '',
                valid_attributes: 'href,src,alt,title,class,id,data-*',
                // Security settings
                allow_html_in_named_anchor: false,
                relative_urls: false,
                convert_urls: false,
                setup: function(editor) {
                    editor.ui.registry.addButton('highlightText', {
                        text: 'Highlight & PDF',
                        tooltip: 'Highlight text and attach a PDF file',
                        icon: 'highlight-bg-color',
                        onAction: function() {
                            let selectedText = editor.selection.getContent({format: 'text'});

                            if (!selectedText) {
                                alert('Please select text to highlight first');
                                return;
                            }

                            // Create a bookmark to remember exact position
                            window.selectionBookmark = editor.selection.getBookmark(3, true);
                            window.selectedText = selectedText;

                            showPdfAttachmentModal(selectedText, editor);
                        }
                    });
                },
                init_instance_callback: function(editor) {
                    // Store reference for later use
                    window.tinyMceEditor = editor;

                    // Force visibility of editor container
                    const editorContainer = document.querySelector('.tox-tinymce');
                    if (editorContainer) {
                        editorContainer.style.visibility = 'visible';
                        editorContainer.style.display = 'block';
                        editorContainer.style.opacity = '1';
                    }

                    // Load content from global variable
                    if (window.documentContent) {
                        editor.setContent(window.documentContent);
                    }

                    // Focus editor so it's ready to edit
                    setTimeout(function() {
                        editor.focus();
                    }, 100);
                }
            });

            function saveTinyMceContent() {
                if (window.tinyMceEditor) {
                    const content = window.tinyMceEditor.getContent();

                    if (!content || content.trim() === '') {
                        alert('No content to save. Please add some content to the editor.');
                        return;
                    }

                    @this.call('saveDocument', content);
                } else {
                    alert('Editor not loaded. Please wait and try again.');
                }
            }

            // Auto-reload page after save to show updated content
            const originalSave = saveTinyMceContent;
            saveTinyMceContent = function() {
                originalSave.call(this);
                setTimeout(() => {
                    location.reload();
                }, 1500);
            };

            // Listen for header action button click (if exists)
            document.addEventListener('click', function(e) {
                if (e.target.closest('[data-testid="action-button"]') ||
                    (e.target.textContent && e.target.textContent.includes('Save Document'))) {
                    const btn = e.target.closest('button');
                    if (btn && btn.textContent.includes('Save Document')) {
                        saveTinyMceContent();
                        e.preventDefault();
                        e.stopPropagation();
                    }
                }
            });

            function showPdfAttachmentModal(selectedText, editor) {
                // Store editor reference globally for modal button
                window.currentEditor = editor;

                // Create a modal dialog
                const modal = document.createElement('div');
                modal.id = 'pdf-attachment-modal';
                modal.className = 'fixed inset-0 bg-black/50 flex items-center justify-center z-50';
                modal.innerHTML = `
                    <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                        <h3 class="text-lg font-semibold mb-4">Attach PDF to Highlight</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Selected text: <strong>"${selectedText}"</strong>
                        </p>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Upload PDF File</label>
                            <input type="file" id="pdfInput" accept=".pdf" class="w-full border rounded-lg p-2">
                        </div>
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Notes (Optional)</label>
                            <textarea id="noteInput" class="w-full border rounded-lg p-2" rows="3" placeholder="Add notes about this highlight..."></textarea>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button onclick="closePdfModal()" class="px-4 py-2 bg-gray-300 dark:bg-gray-700 rounded-lg">
                                Cancel
                            </button>
                            <button onclick="attachPdf('{{ $document->id }}', '${selectedText}', window.currentEditor)" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                Attach
                            </button>
                        </div>
                    </div>
                `;

                document.body.appendChild(modal);
            }

            function closePdfModal() {
                const modal = document.getElementById('pdf-attachment-modal');
                if (modal) modal.remove();
            }

            function attachPdf(documentId, highlightedText, editor) {
                const pdfInput = document.getElementById('pdfInput');
                const noteInput = document.getElementById('noteInput');

                if (!pdfInput.files.length) {
                    alert('Please select a PDF file');
                    return;
                }

                const formData = new FormData();
                formData.append('document_id', documentId);
                formData.append('highlighted_text', highlightedText);
                formData.append('pdf_file', pdfInput.files[0]);
                formData.append('notes', noteInput.value);

                // Send to server
                fetch('{{ route("documents.attach-pdf") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Create simple anchor link - styling handled by CSS
                        const pdfUrl = `{{ url('/documents') }}/${documentId}/highlight/${data.highlight_id}/pdf`;
                        const highlightedHtml = `<a href="${pdfUrl}">${highlightedText}</a>`;

                        // Restore the exact bookmark position and replace selection
                        if (window.selectionBookmark) {
                            editor.selection.moveToBookmark(window.selectionBookmark);
                        }

                        // Replace the selected text with link version
                        editor.selection.setContent(highlightedHtml);
                        editor.setDirty(true);

                        // Clear stored selection
                        window.selectionBookmark = null;
                        window.selectedText = null;

                        alert('PDF attached successfully!');
                        closePdfModal();
                    } else {
                        alert('Error: ' + (data.message || 'Failed to attach PDF'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error attaching PDF');
                });
            }

        </script>
        <style>
            #tinymce-editor {
                min-height: 500px !important;
                display: block !important;
                width: 100% !important;
                visibility: visible !important;
                opacity: 1 !important;
                height: auto !important;
            }

            textarea#tinymce-editor {
                display: none !important;
            }

            #editor-container {
                min-height: 550px !important;
                display: block !important;
                width: 100% !important;
                visibility: visible !important;
                opacity: 1 !important;
            }

            .tox-tinymce {
                border: 1px solid #ccc !important;
                visibility: visible !important;
                display: block !important;
                opacity: 1 !important;
                position: relative !important;
                z-index: 10 !important;
            }

            .tox-editor-container {
                visibility: visible !important;
                display: block !important;
                opacity: 1 !important;
            }

            .tox-toolbar {
                visibility: visible !important;
                display: flex !important;
                opacity: 1 !important;
            }

            .tox-toolbar-overlord {
                visibility: visible !important;
                display: block !important;
                opacity: 1 !important;
            }

            .tox-edit-area {
                visibility: visible !important;
                display: block !important;
                opacity: 1 !important;
                height: 500px !important;
                min-height: 500px !important;
            }

            .tox-edit-area__iframe {
                visibility: visible !important;
                display: block !important;
                opacity: 1 !important;
                height: 500px !important;
                min-height: 500px !important;
            }

            /* Anchor links styling */
            a {
                color: #0066cc !important;
                text-decoration: underline !important;
                cursor: pointer !important;
            }

            a:hover {
                color: #0052a3 !important;
            }
        </style>
    @endpush
</x-filament-panels::page>
