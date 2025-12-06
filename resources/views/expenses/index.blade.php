@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" id="filter-status">
                    <option value="all">@lang('app.all')</option>
                    <option value="pending">@lang('app.pending')</option>
                    <option value="approved">@lang('app.approved')</option>
                    <option value="rejected">@lang('app.rejected')</option>
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- SEARCH BY TASK START -->
        <div class="task-search d-flex  py-1 px-lg-3 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                        placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

        <!-- MORE FILTERS START -->
        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.employee')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" id="employee2" data-live-search="true" data-container="body" data-size="8">
                                <option value="all">@lang('app.all')</option>
                            @foreach ($employees as $item)
                                    <x-user-option :user="$item" />
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.project')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="project_id" id="project_id2" data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 " for="usr">@lang('app.category')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="category_id" id="category_id" data-container="body" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

        </x-filters.more-filter-box>
        <!-- MORE FILTERS END -->

    </x-filters.filter-box>

@endsection

@php
$addExpensesPermission = user()->permission('add_expenses');
$recurringExpensesPermission = user()->permission('manage_recurring_expense');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addExpensesPermission == 'all' || $addExpensesPermission == 'added')
                    <x-forms.link-primary :link="route('expenses.create')" class="mr-3 float-left openRightModal"
                        icon="plus">
                        @lang('modules.expenses.addExpense')
                    </x-forms.link-primary>
                @endif
                @if ($recurringExpensesPermission == 'all')
                    <x-forms.link-secondary :link="route('recurring-expenses.index')" class="mr-3 float-left"
                        icon="sync">
                        @lang('app.menu.expensesRecurring')
                    </x-forms.link-secondary>
                @endif
                @if ($addExpensesPermission == 'all' || $addExpensesPermission == 'added')
                    <x-forms.link-secondary :link="route('expenses.import')" class="mr-3 float-left openRightModal"
                                            icon="file-upload">
                        @lang('app.importExcel')
                    </x-forms.link-secondary>
                @endif
                @if ($addExpensesPermission == 'all' || $addExpensesPermission == 'added')
                    <x-forms.button-secondary class="mr-3 float-left" id="upload-scan-btn" icon="camera">
                        Upload File and Scan
                    </x-forms.button-secondary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="change-status">@lang('modules.tasks.changeStatus')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
                <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                    <select name="status" class="form-control select-picker">
                        <option value="pending">@lang('app.pending')</option>
                        <option value="approved">@lang('app.approved')</option>
                        <option value="rejected">@lang('app.rejected')</option>
                    </select>
                </div>
            </x-datatable.actions>

        </div>

        <!-- Add Task Export Buttons End -->
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

    <!-- OCR Upload and Scan Modal -->
    <div class="modal fade" id="ocrScanModal" tabindex="-1" role="dialog" aria-labelledby="ocrScanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ocrScanModalLabel">
                        <i class="fa fa-camera mr-2"></i>Upload File and Scan with OCR
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- File Upload Section -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="ocrFileInput" class="f-14 text-dark-grey mb-12">
                                    <i class="fa fa-upload mr-1"></i>Select Image File
                                </label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="ocrFileInput" accept="image/*">
                                    <label class="custom-file-label" for="ocrFileInput">Choose image file...</label>
                                </div>
                                <small class="form-text text-muted">
                                    Supported formats: JPG, PNG, GIF, BMP, TIFF (Max size: 10MB)
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Image Preview Section -->
                    <div class="row" id="imagePreviewSection" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">
                                    <i class="fa fa-image mr-1"></i>Image Preview
                                </label>
                                <div class="text-center">
                                    <img id="imagePreview" src="" alt="Image Preview" class="img-fluid" style="max-height: 300px; border: 1px solid #ddd; border-radius: 4px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OCR Controls Section -->
                    <div class="row" id="ocrControlsSection" style="display: none;">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ocrLanguage" class="f-14 text-dark-grey mb-12">
                                    <i class="fa fa-language mr-1"></i>OCR Language
                                </label>
                                <select class="form-control select-picker" id="ocrLanguage">
                                    <option value="eng">English</option>
                                    <option value="chi_sim">Chinese (Simplified)</option>
                                    <option value="chi_tra">Chinese (Traditional)</option>
                                    <option value="fra">French</option>
                                    <option value="deu">German</option>
                                    <option value="spa">Spanish</option>
                                    <option value="jpn">Japanese</option>
                                    <option value="kor">Korean</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" id="startOcrBtn">
                                    <i class="fa fa-search mr-1"></i>Start OCR Scan
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- OCR Progress Section -->
                    <div class="row" id="ocrProgressSection" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">
                                    <i class="fa fa-spinner fa-spin mr-1"></i>OCR Processing...
                                </label>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                                </div>
                                <small class="form-text text-muted">Please wait while we process your image...</small>
                            </div>
                        </div>
                    </div>

                    <!-- OCR Results Section -->
                    <div class="row" id="ocrResultsSection" style="display: none;">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-12">
                                    <i class="fa fa-file-text mr-1"></i>OCR Scan Results
                                </label>
                                <textarea class="form-control" id="ocrResultText" rows="50" placeholder="OCR results will appear here..."></textarea>
                                <small class="form-text text-muted">
                                    <i class="fa fa-info-circle mr-1"></i>You can edit the text above if needed
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Error Section -->
                    <div class="row" id="ocrErrorSection" style="display: none;">
                        <div class="col-md-12">
                            <div class="alert alert-danger" role="alert">
                                <i class="fa fa-exclamation-triangle mr-1"></i>
                                <span id="ocrErrorMessage">An error occurred during OCR processing.</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i>Close
                    </button>
                    <button type="button" class="btn btn-success" id="copyResultBtn" style="display: none;">
                        <i class="fa fa-copy mr-1"></i>Copy to Clipboard
                    </button>
                    <button type="button" class="btn btn-primary" id="resetOcrBtn" style="display: none;">
                        <i class="fa fa-refresh mr-1"></i>Scan Another Image
                    </button>
                    <button type="button" class="btn btn-warning" id="fulfillExpenseBtn" style="display: none;">
                        <i class="fa fa-plus mr-1"></i>Fullfill to new expense
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <!-- OCR Upload and Scan JavaScript -->
    <script>
        $(document).ready(function() {
            // OCR Modal functionality
            $('#upload-scan-btn').on('click', function() {
                $('#ocrScanModal').modal('show');
                resetOcrModal();
            });

            // File input change handler
            $('#ocrFileInput').on('change', function() {
                const file = this.files[0];
                if (file) {
                    // Validate file type
                    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff'];
                    if (!allowedTypes.includes(file.type)) {
                        showOcrError('Please select a valid image file (JPG, PNG, GIF, BMP, TIFF)');
                        return;
                    }

                    // Validate file size (10MB max)
                    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
                    if (file.size > maxSize) {
                        showOcrError('File size must be less than 10MB');
                        return;
                    }

                    // Update file label
                    $(this).next('.custom-file-label').text(file.name);

                    // Show image preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result);
                        $('#imagePreviewSection').show();
                        $('#ocrControlsSection').show();
                        hideOcrError();
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Start OCR button handler
            $('#startOcrBtn').on('click', function() {
                const fileInput = document.getElementById('ocrFileInput');
                const file = fileInput.files[0];

                if (!file) {
                    showOcrError('Please select an image file first');
                    return;
                }

                const language = $('#ocrLanguage').val();
                startOcrProcessing(file, language);
            });

            // Copy result button handler
            $('#copyResultBtn').on('click', function() {
                const resultText = $('#ocrResultText').val();
                if (resultText) {
                    navigator.clipboard.writeText(resultText).then(function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Copied!',
                            text: 'OCR result copied to clipboard',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }).catch(function() {
                        // Fallback for older browsers
                        $('#ocrResultText').select();
                        document.execCommand('copy');
                        Swal.fire({
                            icon: 'success',
                            title: 'Copied!',
                            text: 'OCR result copied to clipboard',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    });
                }
            });

            // Reset OCR button handler
            $('#resetOcrBtn').on('click', function() {
                resetOcrModal();
            });

            // Fullfill to new expense button handler
            $('#fulfillExpenseBtn').on('click', function() {
                const ocrText = $('#ocrResultText').val();
                if (!ocrText.trim()) {
                    alert('请先进行OCR识别获取文本内容');
                    return;
                }

                // Parse OCR text and auto-fill expense form
                parseAndFillExpenseForm(ocrText);
            });

            // Function to start OCR processing
            function startOcrProcessing(file, language) {
                // Show progress section
                $('#ocrControlsSection').hide();
                $('#ocrProgressSection').show();
                hideOcrError();

                // Store the original file for later use
                window.ocrOriginalFile = file;

                // Create FormData for file upload
                const formData = new FormData();
                formData.append('image', file);
                formData.append('language', language);
                formData.append('_token', '{{ csrf_token() }}');

                // Make AJAX request to OCR endpoint
                $.ajax({
                    url: '{{ route("expenses.ocr") }}',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    timeout: 60000, // 60 seconds timeout
                    success: function(response) {
                        $('#ocrProgressSection').hide();

                        if (response.success) {
                            $('#ocrResultText').val(response.text);
                            // Auto-adjust textarea height to fit content
                            autoResizeTextarea($('#ocrResultText')[0]);
                            $('#ocrResultsSection').show();
                            $('#copyResultBtn').show();
                            $('#resetOcrBtn').show();
                            $('#fulfillExpenseBtn').show();

                            Swal.fire({
                                icon: 'success',
                                title: 'OCR Complete!',
                                text: 'Text extraction completed successfully',
                                timer: 3000,
                                showConfirmButton: false
                            });
                        } else {
                            showOcrError(response.message || 'OCR processing failed');
                            $('#ocrControlsSection').show();
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#ocrProgressSection').hide();
                        $('#ocrControlsSection').show();

                        let errorMessage = 'OCR processing failed';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (status === 'timeout') {
                            errorMessage = 'Request timeout. Please try again with a smaller image.';
                        } else if (xhr.status === 413) {
                            errorMessage = 'File too large. Please select a smaller image.';
                        }

                        showOcrError(errorMessage);
                    }
                });
            }

            // Function to show OCR error
            function showOcrError(message) {
                $('#ocrErrorMessage').text(message);
                $('#ocrErrorSection').show();
            }

            // Function to hide OCR error
            function hideOcrError() {
                $('#ocrErrorSection').hide();
            }

            // Function to reset OCR modal
            function resetOcrModal() {
                // Reset file input
                $('#ocrFileInput').val('');
                $('#ocrFileInput').next('.custom-file-label').text('Choose image file...');

                // Reset language selection
                $('#ocrLanguage').val('eng');

                // Reset result text
                $('#ocrResultText').val('');

                // Hide all sections except file upload
                $('#imagePreviewSection').hide();
                $('#ocrControlsSection').hide();
                $('#ocrProgressSection').hide();
                $('#ocrResultsSection').hide();
                $('#ocrErrorSection').hide();

                // Hide action buttons
                $('#copyResultBtn').hide();
                $('#resetOcrBtn').hide();
                $('#fulfillExpenseBtn').hide();

                // Clear image preview
                $('#imagePreview').attr('src', '');
            }

            // Modal close event handler
            $('#ocrScanModal').on('hidden.bs.modal', function() {
                resetOcrModal();
            });

            // Function to parse OCR text and auto-fill expense form
            function parseAndFillExpenseForm(ocrText) {
                // Parse the OCR text to extract expense information
                const expenseData = parseExpenseData(ocrText);

                // Store the parsed data in sessionStorage for the create page
                sessionStorage.setItem('autoFillExpenseData', JSON.stringify(expenseData));

                // Store the original OCR text for scan content display
                sessionStorage.setItem('originalOcrText', ocrText);

                // Store the original image file information if available
                if (window.ocrOriginalFile) {
                    // Convert file to base64 for storage
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const imageData = {
                            name: window.ocrOriginalFile.name,
                            type: window.ocrOriginalFile.type,
                            size: window.ocrOriginalFile.size,
                            data: e.target.result // base64 data
                        };
                        sessionStorage.setItem('autoFillImageData', JSON.stringify(imageData));

                        // Close the OCR modal and redirect after storing image data
                        $('#ocrScanModal').modal('hide');
                        const createUrl = "{{ route('expenses.create') }}";
                        window.location.href = createUrl;
                    };
                    reader.readAsDataURL(window.ocrOriginalFile);
                } else {
                    // No image file, just redirect
                    $('#ocrScanModal').modal('hide');
                    const createUrl = "{{ route('expenses.create') }}";
                    window.location.href = createUrl;
                }
            }

            // Function to parse expense data from OCR text with intelligent field extraction
            function parseExpenseData(text) {
                const data = {
                    item_name: '',
                    price: '',
                    purchase_date: '',
                    purchase_from: '',
                    category: '',
                    project: '',
                    bank_account: '',
                    employee: '',
                    description: '' // Will be extracted specifically
                };

                // Clean and normalize text for better parsing
                const cleanText = text.replace(/\s+/g, ' ').trim();
                const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);

                // Enhanced price extraction with multiple currency support and patterns
                const pricePatterns = [
                    // Currency symbols with amounts
                    /(?:total|amount|price|cost|sum|subtotal|grand\s*total)[\s:]*[\$¥€£￥]?\s*(\d+(?:[,\.]\d{2,3})*(?:\.\d{2})?)/gi,
                    /[\$¥€£￥]\s*(\d+(?:[,\.]\d{2,3})*(?:\.\d{2})?)/gi,
                    /(\d+(?:[,\.]\d{2,3})*(?:\.\d{2})?)\s*[\$¥€£￥]/gi,
                    // Written currency names
                    /(\d+(?:[,\.]\d{2,3})*(?:\.\d{2})?)\s*(?:dollars?|yuan|euros?|pounds?|rmb|usd|eur|gbp|cny)/gi,
                    // Amount with decimal patterns
                    /(?:amount|total|price|cost)[\s:]*(\d+(?:[,\.]\d{2,3})*(?:\.\d{2})?)/gi,
                    // Standalone monetary amounts (last resort)
                    /\b(\d+\.\d{2})\b/g
                ];

                for (const pattern of pricePatterns) {
                    const matches = [...cleanText.matchAll(pattern)];
                    if (matches.length > 0) {
                        // Get the largest amount (likely the total)
                        const amounts = matches.map(match => parseFloat(match[1].replace(/,/g, '')));
                        data.price = Math.max(...amounts).toString();
                        break;
                    }
                }

                // Enhanced date extraction with multiple formats and languages
                const datePatterns = [
                    // ISO format dates
                    /(\d{4}[-\/]\d{1,2}[-\/]\d{1,2})/g,
                    // US format dates
                    /(\d{1,2}[-\/]\d{1,2}[-\/]\d{4})/g,
                    // Short year dates
                    /(\d{1,2}[-\/]\d{1,2}[-\/]\d{2})/g,
                    // Month name formats (English)
                    /(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s,]*\d{1,2}[\s,]*\d{4}/gi,
                    /\d{1,2}[\s,]+(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*[\s,]*\d{4}/gi,
                    // Chinese date formats
                    /(\d{4})年(\d{1,2})月(\d{1,2})日/g,
                    // Date with context
                    /(?:date|时间|日期)[\s:]*(\d{4}[-\/年]\d{1,2}[-\/月]\d{1,2}[日]?)/gi,
                    // Receipt date patterns
                    /(?:receipt|invoice|bill)\s+(?:date|时间)[\s:]*([^\n\r]+)/gi
                ];

                for (const pattern of datePatterns) {
                    const match = pattern.exec(cleanText);
                    if (match) {
                        data.purchase_date = match[0];
                        break;
                    }
                }

                // Enhanced vendor/store name extraction - look for specific patterns
                const vendorPatterns = [
                    // Look for company names after specific keywords
                    /(?:purchased\s+from|vendor|supplier|store|shop|company|merchant|from|at)[\s:]+([A-Za-z\u4e00-\u9fff\s&\-\.]+?)(?:\n|$|,|\d|address|tel|phone)/gi,
                    // Look for standalone company names (like "Hunufa")
                    /^([A-Za-z\u4e00-\u9fff]{3,20})$/gm,
                    // Receipt header (usually first line with company name)
                    /^([A-Za-z\u4e00-\u9fff\s&\-\.]{3,30})(?:\n|receipt|invoice|bill|store|shop)/gi,
                    // Store/restaurant/cafe patterns
                    /([A-Z\u4e00-\u9fff][A-Za-z\u4e00-\u9fff\s&\-\.]*?)\s+(?:store|shop|market|restaurant|cafe|hotel|mall|center|ltd|inc|corp|co)/gi,
                    // Address-based extraction (company name before address)
                    /([A-Za-z\u4e00-\u9fff\s&\-\.]{3,30})\s+(?:\d+.*?(?:street|road|avenue|blvd|st|rd|ave))/gi
                ];

                for (const pattern of vendorPatterns) {
                    const match = pattern.exec(cleanText);
                    if (match) {
                        const vendor = match[1].trim().replace(/[^\w\s&\-\.]/g, '');
                        // Skip common words that are not vendor names
                        if (vendor && !['category', 'price', 'date', 'item', 'name', 'disposable'].includes(vendor.toLowerCase())) {
                            data.purchase_from = vendor;
                            break;
                        }
                    }
                }

                // Enhanced item name extraction - look for "Payment To Vendor" pattern
                const itemPatterns = [
                    // Look for "Payment To Vendor" or similar patterns
                    /(payment\s+to\s+vendor|payment\s+to|vendor\s+payment)/gi,
                    // Explicit item indicators
                    /(?:item\s+name|item|product|service|description|商品|产品|服务)[\s:]+([A-Za-z\u4e00-\u9fff\s\-\.]+?)(?:\n|$|,|\d|price|amount)/gi,
                    // Item with price pattern
                    /^([A-Za-z\u4e00-\u9fff\s\-\.]+?)(?:\s+[\$¥€£￥]\d+|\s+\d+[\$¥€£￥]|\s+\d+\.\d{2})/gm,
                    // Menu item patterns
                    /(\d+[\.\)]\s*)?([A-Za-z\u4e00-\u9fff\s\-\.]{3,40}?)(?:\s+[\$¥€£￥]?\d+(?:\.\d{2})?)/gm,
                    // Product line patterns
                    /^([A-Za-z\u4e00-\u9fff\s\-\.]{3,40})\s*$/gm
                ];

                for (const pattern of itemPatterns) {
                    const match = pattern.exec(cleanText);
                    if (match) {
                        const itemName = (match[1] || match[0]).trim();
                        if (itemName.length >= 3 && itemName.length <= 50) {
                            data.item_name = itemName;
                            break;
                        }
                    }
                }

                // Category extraction based on keywords
                const categoryKeywords = {
                    'Food & Dining': ['restaurant', 'cafe', 'food', 'dining', 'meal', 'lunch', 'dinner', 'breakfast', 'pizza', 'burger', 'coffee', '餐厅', '咖啡', '食物'],
                    'Transportation': ['taxi', 'uber', 'lyft', 'bus', 'train', 'flight', 'airline', 'transport', 'parking', 'gas', 'fuel', '出租车', '交通', '停车'],
                    'Office Supplies': ['office', 'supplies', 'paper', 'pen', 'printer', 'computer', 'software', 'stationery', '办公', '文具'],
                    'Travel': ['hotel', 'accommodation', 'travel', 'trip', 'vacation', 'flight', 'booking', '酒店', '旅行'],
                    'Utilities': ['electricity', 'water', 'gas', 'internet', 'phone', 'utility', '水电', '网络'],
                    'Marketing': ['advertising', 'marketing', 'promotion', 'ad', 'campaign', '广告', '营销'],
                    'Equipment': ['equipment', 'hardware', 'machinery', 'tools', '设备', '工具'],
                    'Professional Services': ['consulting', 'legal', 'accounting', 'professional', 'service', '咨询', '法律', '会计']
                };

                for (const [category, keywords] of Object.entries(categoryKeywords)) {
                    for (const keyword of keywords) {
                        if (cleanText.toLowerCase().includes(keyword.toLowerCase())) {
                            data.category = category;
                            break;
                        }
                    }
                    if (data.category) break;
                }

                // Project extraction (look for project codes or names)
                const projectPatterns = [
                    /(?:project|proj|项目)[\s:]*([A-Za-z\u4e00-\u9fff0-9\-]+)/gi,
                    /(?:job|task|work)\s+(?:number|no|#)[\s:]*([A-Za-z0-9\-]+)/gi,
                    /#([A-Za-z0-9\-]{3,20})/g
                ];

                for (const pattern of projectPatterns) {
                    const match = pattern.exec(cleanText);
                    if (match) {
                        data.project = match[1].trim();
                        break;
                    }
                }

                // Employee extraction (look for employee names or IDs)
                const employeePatterns = [
                    /(?:employee|staff|emp|员工)[\s:]*([A-Za-z\u4e00-\u9fff\s]+?)(?:\n|$|,|\d)/gi,
                    /(?:submitted by|requested by|申请人)[\s:]*([A-Za-z\u4e00-\u9fff\s]+?)(?:\n|$|,|\d)/gi
                ];

                for (const pattern of employeePatterns) {
                    const match = pattern.exec(cleanText);
                    if (match) {
                        data.employee = match[1].trim();
                        break;
                    }
                }

                // Bank account extraction (look for account numbers or bank names)
                const bankPatterns = [
                    /(?:bank|account|card|银行|账户)[\s:]*([A-Za-z\u4e00-\u9fff\s]+?)(?:\n|$|,|\d)/gi,
                    /(?:paid by|payment method|支付方式)[\s:]*([A-Za-z\u4e00-\u9fff\s]+?)(?:\n|$|,|\d)/gi
                ];

                for (const pattern of bankPatterns) {
                    const match = pattern.exec(cleanText);
                    if (match) {
                        data.bank_account = match[1].trim();
                        break;
                    }
                }

                // Fallback item name extraction if still empty
                if (!data.item_name && lines.length > 0) {
                    for (const line of lines) {
                        // Skip lines that look like dates, prices, addresses, or phone numbers
                        if (!/\d{4}[-\/年]\d{1,2}[-\/月]\d{1,2}/.test(line) &&
                            !/[\$¥€£￥]\d+/.test(line) &&
                            !/\d+[\$¥€£￥]/.test(line) &&
                            !/^\d+[\s\-\(\)]+/.test(line) && // phone numbers
                            !/\d+.*(?:street|road|avenue|st|rd|ave|路|街|区)/.test(line) && // addresses
                            line.length >= 3 && line.length <= 50 &&
                            !/^[A-Z]{2,}$/.test(line)) { // avoid all caps abbreviations
                            data.item_name = line.trim();
                            break;
                        }
                    }
                }

                // Enhanced description extraction - look for specific patterns
                const descriptionPatterns = [
                    // Pattern for codes with descriptions like "E3-BO#317 ,Excess Payment = 60000"
                    /([A-Z]\d+[-#][A-Z0-9#]+\s*[,，]\s*[^,\n]*(?:payment|excess|amount)[^,\n]*(?:=\s*\d+)?)/gi,
                    // Pattern for transaction descriptions with codes
                    /([A-Z]\d+[-#][A-Z0-9#]+[^,\n]*)/gi,
                    // Pattern for payment descriptions
                    /((?:excess\s*)?payment[^,\n]*(?:=\s*\d+)?)/gi,
                    // Pattern for reference codes with amounts
                    /([A-Z]\d+[-#][A-Z0-9#]+.*?\d+)/gi
                ];

                for (const pattern of descriptionPatterns) {
                    const matches = [...cleanText.matchAll(pattern)];
                    if (matches.length > 0) {
                        // Take the longest match as it's likely the most complete description
                        const descriptions = matches.map(match => match[1].trim());
                        data.description = descriptions.reduce((a, b) => a.length > b.length ? a : b);
                        break;
                    }
                }

                // If no specific description pattern found, look for lines with reference codes
                if (!data.description) {
                    for (const line of lines) {
                        if (/[A-Z]\d+[-#][A-Z0-9#]+/i.test(line) && line.length > 10) {
                            data.description = line.trim();
                            break;
                        }
                    }
                }

                // Clean up extracted data
                Object.keys(data).forEach(key => {
                    if (typeof data[key] === 'string') {
                        // For description, preserve more characters including special ones
                        if (key === 'description') {
                            data[key] = data[key].replace(/[^\w\s\-\.\#=,，\u4e00-\u9fff]/g, '').trim();
                        } else {
                            data[key] = data[key].replace(/[^\w\s\-\.\u4e00-\u9fff]/g, '').trim();
                        }
                    }
                });

                return data;
            }

            // Function to auto-resize textarea based on content
            function autoResizeTextarea(textarea) {
                textarea.style.height = 'auto';
                textarea.style.height = Math.max(200, textarea.scrollHeight) + 'px';
            }

            // Add input event listener for real-time resizing
            $('#ocrResultText').on('input', function() {
                autoResizeTextarea(this);
            });
        });
    </script>

    <script>
        $('#expenses-table').on('preXhr.dt', function(e, settings, data) {
            var dateRangePicker = $('#datatableRange').data('daterangepicker');
            var startDate = $('#datatableRange').val();

            if (startDate == '') {
                startDate = null;
                endDate = null;
            } else {
                startDate = dateRangePicker.startDate.format('{{ company()->moment_date_format }}');
                endDate = dateRangePicker.endDate.format('{{ company()->moment_date_format }}');
            }

            var status = $('#filter-status').val();
            var searchText = $('#search-text-field').val();
            var employee = $('#employee2').val();
            var projectId = $('#project_id2').val();
            var categoryId = $('#category_id').val();

            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['status'] = status;
            data['employee'] = employee;
            data['projectId'] = projectId;
            data['categoryId'] = categoryId;
            data['searchText'] = searchText;
        });
        const showTable = () => {
            window.LaravelDataTables["expenses-table"].draw(false);
        }

        $('#filter-status, #employee2,#project_id2,#category_id')
            .on('change keyup',
                function() {
                    if ($('#filter-status').val() != "all") {
                        $('#reset-filters').removeClass('d-none');
                        showTable();
                    } else {
                        $('#reset-filters').addClass('d-none');
                        showTable();
                    }
                });

        $('#search-text-field').on('keyup', function() {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });


        $('#reset-filters-2').click(function() {
            $('#filter-form')[0].reset();

            $('.filter-box #status').val('not finished');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });


        $('#quick-action-type').change(function() {
            const actionValue = $(this).val();
            if (actionValue != '') {
                $('#quick-action-apply').removeAttr('disabled');

                if (actionValue == 'change-status') {
                    $('.quick-action-field').addClass('d-none');
                    $('#change-status-action').removeClass('d-none');
                } else {
                    $('.quick-action-field').addClass('d-none');
                }
            } else {
                $('#quick-action-apply').attr('disabled', true);
                $('.quick-action-field').addClass('d-none');
            }
        });

        $('#quick-action-apply').click(function() {
            const actionValue = $('#quick-action-type').val();
            if (actionValue == 'delete') {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: "@lang('messages.recoverRecord')",
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('messages.confirmDelete')",
                    cancelButtonText: "@lang('app.cancel')",
                    customClass: {
                        confirmButton: 'btn btn-primary mr-3',
                        cancelButton: 'btn btn-secondary'
                    },
                    showClass: {
                        popup: 'swal2-noanimation',
                        backdrop: 'swal2-noanimation'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        applyQuickAction();
                    }
                });

            } else {
                applyQuickAction();
            }
        });

        $('body').on('change', '.change-expense-status', function() {
            var id = $(this).data('expense-id');
            var url = "{{ route('expenses.change_status') }}";

            var token = "{{ csrf_token() }}";
            var status = $(this).val();

            if (typeof id !== 'undefined') {
                $.easyAjax({
                    url: "{{ route('expenses.change_status') }}",
                    type: "POST",
                    data: {
                        '_token': token,
                        expenseId: id,
                        status: status
                    },

                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                            resetActionButtons();
                            deSelectAll();
                        }
                    }
                });
            }
        });

        $('body').on('click', '.delete-table-row', function() {
            var id = $(this).data('expense-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('expenses.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {
                            '_token': token,
                            '_method': 'DELETE'
                        },
                        success: function(response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        });

        const applyQuickAction = () => {
            var rowdIds = $("#expenses-table input:checkbox:checked").map(function() {
                return $(this).val();
            }).get();

            var url = "{{ route('expenses.apply_quick_action') }}?row_ids=" + rowdIds;

            $.easyAjax({
                url: url,
                container: '#quick-action-form',
                type: "POST",
                disableButton: true,
                buttonSelector: "#quick-action-apply",
                data: $('#quick-action-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        showTable();
                        resetActionButtons();
                        deSelectAll();
                        $('#quick-action-form').hide();
                    }
                }
            })
        };
    </script>
@endpush
