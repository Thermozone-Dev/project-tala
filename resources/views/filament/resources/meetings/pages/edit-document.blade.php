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
                // Track upload state
                window.uploadsInProgress = 0;
            </script>

            <!-- PDF Attachment Progress Section -->
            <div id="attachmentSection" style="display: none;" class="mt-6 p-4 border border-blue-200 dark:border-blue-800 rounded-lg bg-blue-50 dark:bg-blue-900/20">
                <h3 class="text-lg font-semibold mb-4 text-blue-900 dark:text-blue-100">PDF Attachments</h3>

                <!-- Active Uploads -->
                <div id="uploadQueue" class="space-y-3">
                    <!-- Upload items will be added here -->
                </div>

                <!-- Completed Uploads -->
                <div id="completedUploads" class="mt-4 space-y-2">
                    <!-- Completed items will be added here -->
                </div>
            </div>
            <div class="mt-6 flex gap-3 justify-end">
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
                height: 550,
                menubar: false,
                branding: false,
                // GPL License for self-hosted TinyMCE 7
                license_key: 'gpl',
                // Tell TinyMCE where to find its resources
                base_url: '{{ asset("vendor/tinymce") }}',
                // Disable Grammarly interference
                browser_spellcheck: false,
                // TinyMCE 7: Use only essential plugins that are available
                plugins: 'advlist autolink lists link charmap preview anchor searchreplace visualblocks code fullscreen table help wordcount image',
                toolbar: 'undo redo | formatselect | bold italic underline | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | image link | highlightText attachPdf',
                // Image upload configuration
                image_upload_url: '{{ route("documents.upload-image") }}',
                images_upload_handler: function(blobInfo, progress) {
                    return new Promise(function(resolve, reject) {
                        const formData = new FormData();
                        formData.append('file', blobInfo.blob(), blobInfo.filename());
                        formData.append('document_id', '{{ $document->id }}');

                        const xhr = new XMLHttpRequest();
                        xhr.withCredentials = false;

                        xhr.upload.onprogress = function(e) {
                            progress(e.loaded / e.total * 100);
                        };

                        xhr.onload = function() {
                            if (xhr.status === 403) {
                                reject({message: 'HTTP Error: ' + xhr.status, remove: true});
                                return;
                            }
                            if (xhr.status < 200 || xhr.status >= 300) {
                                reject('HTTP Error: ' + xhr.status);
                                return;
                            }
                            const json = JSON.parse(xhr.responseText);
                            if (!json || typeof json.location != 'string') {
                                reject('Invalid JSON: ' + xhr.responseText);
                                return;
                            }
                            resolve(json.location);
                        };

                        xhr.onerror = function() {
                            reject({message: 'Image upload failed', remove: true});
                        };

                        xhr.open('POST', formData.get('file') ? '{{ route("documents.upload-image") }}' : '');
                        xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                        xhr.send(formData);
                    });
                },
                // Color picker configuration
                color_cpicker_callback: function(callback, value) {
                    callback(value);
                },
                // Content CSS for styling
                content_style: `
                    body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; line-height: 1.6; }
                    p { margin: 10px 0; }
                    p[style*="text-align: center"] { text-align: center !important; }
                    p[style*="text-align: right"] { text-align: right !important; }
                    p[style*="text-align: justify"] { text-align: justify !important; }
                    p[style*="text-align: left"] { text-align: left !important; }
                    h1, h2, h3, h4, h5, h6 { margin: 15px 0 10px 0; }
                    ul, ol { margin: 15px 0; padding-left: 40px; }
                    li { margin: 5px 0; }
                    table { border-collapse: collapse; margin: 15px 0; }
                    th, td { border: 1px solid #999; padding: 10px; }
                    th { background-color: #f2f2f2; }
                `,
                // Allow style attribute to preserve alignment and other formatting
                valid_children: '+p[style],+div[style],+h1[style],+h2[style],+h3[style],+h4[style],+h5[style],+h6[style]',
                extended_valid_elements: 'p[style|class],div[style|class],h1[style|class],h2[style|class],h3[style|class],h4[style|class],h5[style|class],h6[style|class]',
                // Security: Block dangerous elements
                invalid_elements: 'script,iframe,embed,object,style,input,form,button',
                valid_attributes: 'href,src,alt,title,class,id,data-*,style',
                // Force style preservation
                force_br_newlines: false,
                paste_as_text: false,
                // Define formats to recognize inline styles
                formats: {
                    alignleft: {
                        selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,li',
                        styles: { 'text-align': 'left' },
                        toggle: true
                    },
                    aligncenter: {
                        selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,li',
                        styles: { 'text-align': 'center' },
                        toggle: true
                    },
                    alignright: {
                        selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,li',
                        styles: { 'text-align': 'right' },
                        toggle: true
                    },
                    alignjustify: {
                        selector: 'p,h1,h2,h3,h4,h5,h6,td,th,div,li',
                        styles: { 'text-align': 'justify' },
                        toggle: true
                    }
                },
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
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Maximum file size: <strong>50 MB</strong></p>
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

                // Add real-time file size validation
                const fileInput = document.getElementById('pdfInput');
                fileInput.addEventListener('change', function() {
                    const MAX_FILE_SIZE = 125 * 1024 * 1024;
                    const file = this.files[0];

                    if (file && file.size > MAX_FILE_SIZE) {
                        const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
                        const errorDiv = document.createElement('div');
                        errorDiv.id = 'file-size-error';
                        errorDiv.style.cssText = 'background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 8px; border-radius: 4px; margin-top: 8px; font-size: 12px;';
                        errorDiv.innerHTML = `⚠️ <strong>File too large:</strong> ${fileSizeMB}MB (max: 125MB)`;

                        // Remove existing error if any
                        const existingError = document.getElementById('file-size-error');
                        if (existingError) existingError.remove();

                        // Add error message after file input
                        fileInput.parentElement.appendChild(errorDiv);
                    } else {
                        // Remove error message if file is valid
                        const existingError = document.getElementById('file-size-error');
                        if (existingError) existingError.remove();
                    }
                });
            }

            function closePdfModal() {
                const modal = document.getElementById('pdf-attachment-modal');
                if (modal) modal.remove();
            }

            function attachPdf(documentId, highlightedText, editor) {
                const pdfInput = document.getElementById('pdfInput');
                const noteInput = document.getElementById('noteInput');
                const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB in bytes

                if (!pdfInput.files.length) {
                    alert('Please select a PDF file');
                    return;
                }

                const file = pdfInput.files[0];

                // Validate file type
                if (file.type !== 'application/pdf') {
                    alert('Only PDF files are allowed.');
                    return;
                }

                // Validate file size
                if (file.size > MAX_FILE_SIZE) {
                    const maxSizeMB = MAX_FILE_SIZE / (1024 * 1024);
                    const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);

                    // Show prominent error notification
                    const errorMsg = `❌ FILE TOO LARGE\n\nYour file is ${fileSizeMB}MB\nMaximum allowed: ${maxSizeMB}MB\n\nPlease select a smaller PDF file.`;
                    alert(errorMsg);

                    // Also update the modal with the error
                    const modal = document.getElementById('pdf-attachment-modal');
                    if (modal) {
                        const errorDiv = document.createElement('div');
                        errorDiv.style.cssText = 'background-color: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 12px; border-radius: 4px; margin-bottom: 12px;';
                        errorDiv.innerHTML = `<strong>⚠️ Error:</strong> File size (${fileSizeMB}MB) exceeds maximum of ${maxSizeMB}MB`;
                        modal.querySelector('div').insertBefore(errorDiv, modal.querySelector('div').firstChild);
                    }
                    return;
                }

                // Close modal immediately after selecting attach
                closePdfModal();

                // Show attachment section
                document.getElementById('attachmentSection').style.display = 'block';

                // Create upload item
                const uploadId = 'upload-' + Date.now();
                const uploadItem = document.createElement('div');
                uploadItem.id = uploadId;
                uploadItem.className = 'p-3 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-700';
                uploadItem.innerHTML = `
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex-1">
                            <p class="font-medium text-sm">${highlightedText.substring(0, 50)}${highlightedText.length > 50 ? '...' : ''}</p>
                            <p class="text-xs text-gray-500">${file.name} (${(file.size / (1024 * 1024)).toFixed(2)}MB)</p>
                        </div>
                        <span class="text-xs font-semibold text-blue-600 dark:text-blue-400">0%</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-blue-600 h-2 rounded-full" style="width: 0%"></div>
                    </div>
                `;
                document.getElementById('uploadQueue').appendChild(uploadItem);

                // Increment uploads in progress
                window.uploadsInProgress++;
                updateSaveButtonState();

                // Upload with progress
                const formData = new FormData();
                formData.append('document_id', documentId);
                formData.append('highlighted_text', highlightedText);
                formData.append('pdf_file', file);
                formData.append('notes', noteInput.value);

                const xhr = new XMLHttpRequest();

                // Track progress
                xhr.upload.addEventListener('progress', function(e) {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        const uploadEl = document.getElementById(uploadId);
                        if (uploadEl) {
                            uploadEl.querySelector('span').textContent = Math.round(percentComplete) + '%';
                            uploadEl.querySelector('div div').style.width = percentComplete + '%';
                        }
                    }
                });

                // Handle completion
                xhr.onload = function() {
                    window.uploadsInProgress--;
                    const uploadEl = document.getElementById(uploadId);

                    if (xhr.status === 200) {
                        const data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            // Create anchor link
                            const pdfUrl = `{{ url('/documents') }}/${documentId}/highlight/${data.highlight_id}/pdf`;
                            const highlightedHtml = `<a href="${pdfUrl}" target="_blank" rel="noopener noreferrer" class="pdf-link">${highlightedText}</a>`;

                            // Restore bookmark and insert link
                            if (window.selectionBookmark) {
                                editor.selection.moveToBookmark(window.selectionBookmark);
                            }
                            editor.selection.setContent(highlightedHtml);
                            editor.setDirty(true);

                            // Move to completed
                            uploadEl.className = 'bg-green-50 dark:bg-green-900/20 rounded border border-green-200 dark:border-green-700 col-6 p-5';
                            uploadEl.dataset.highlightId = data.highlight_id;
                            uploadEl.innerHTML = `
                                <div class="flex space-between p-5">
                                    <p class="text-xs text-green-700 dark:text-green-300">${file.name}</p>
                                </div>
                            `;

                            // Clear inputs
                            window.selectionBookmark = null;
                            window.selectedText = null;
                        }
                    } else {
                        uploadEl.className = 'p-3 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-700';
                        uploadEl.innerHTML = `
                            <p class="text-sm font-medium text-red-900 dark:text-red-100">Upload failed</p>
                            <p class="text-xs text-red-700 dark:text-red-300">${xhr.responseText}</p>
                        `;
                    }

                    updateSaveButtonState();
                };

                xhr.onerror = function() {
                    window.uploadsInProgress--;
                    const uploadEl = document.getElementById(uploadId);
                    uploadEl.className = 'p-3 bg-red-50 dark:bg-red-900/20 rounded border border-red-200 dark:border-red-700';
                    uploadEl.innerHTML = `<p class="text-sm font-medium text-red-900 dark:text-red-100">Upload failed</p>`;
                    updateSaveButtonState();
                };

                // Send request
                xhr.open('POST', '{{ route("documents.attach-pdf") }}');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                xhr.send(formData);
            }

            function updateSaveButtonState() {
                const saveBtn = document.getElementById('save-document-btn');
                if (window.uploadsInProgress > 0) {
                    saveBtn.disabled = true;
                    saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    saveBtn.textContent = 'Uploading... (' + window.uploadsInProgress + ')';
                } else {
                    saveBtn.disabled = false;
                    saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    saveBtn.textContent = 'Save Document';
                }
            }

        </script>
    @endpush

    @push('styles')
        <style scoped>
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
            #editor-container a {
                color: #0066cc !important;
                text-decoration: underline !important;
                cursor: pointer !important;
            }

            a:hover {
                color: #0052a3 !important;
            }

            /* ul, ol {
                margin: 15px 0 !important;
                padding-left: 40px !important;
            }

            li {
                padding: 8px 0 !important;
                line-height: 1.6 !important;
            } */

            /* Heading styling */
            h1, h2, h3, h4, h5, h6 {
                margin-top: 15px !important;
                margin-bottom: 10px !important;
                line-height: 1.4 !important;
                font-weight: bold !important;
            }

            h1 { font-size: 28px !important; }
            h2 { font-size: 24px !important; }
            h3 { font-size: 20px !important; }
            h4 { font-size: 18px !important; }
            h5 { font-size: 16px !important; }
            h6 { font-size: 14px !important; }

            /* Table styling */
            table {
                border-collapse: collapse !important;
                width: 100% !important;
                margin: 15px 0 !important;
            }

            table, th, td {
                border: 1px solid #999 !important;
            }

            th {
                background-color: #f2f2f2 !important;
                padding: 12px !important;
                text-align: left !important;
                font-weight: bold !important;
            }

            td {
                padding: 12px !important;
                vertical-align: top !important;
            }

            /* Text alignment */
            p[style*="text-align: center"] {
                text-align: center !important;
            }

            p[style*="text-align: right"] {
                text-align: right !important;
            }

            p[style*="text-align: justify"] {
                text-align: justify !important;
            }

            /* Font size */
            span[style*="font-size"] {
                line-height: 1.6 !important;
            }

            /* Paragraph spacing */
            p {
                margin: 10px 0 !important;
                line-height: 1.6 !important;
                font-size: 14px !important;
            }

            /* Document content base size */
            .document-content {
                font-size: 14px !important;
            }
        </style>
    @endpush
</x-filament-panels::page>

