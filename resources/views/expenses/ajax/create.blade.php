@php
$addExpenseCategoryPermission = user()->permission('manage_expense_category');
@endphp

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-expense-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal  border-bottom-grey">
                    @lang('app.expenseDetails')</h4>
                <div class="row p-20">
                    <div class="col-md-6 col-lg-3">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.expenses.itemName')"
                            fieldName="item_name" fieldRequired="true" fieldId="item_name"
                            :fieldPlaceholder="__('placeholders.expense.item')" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        @if(isset($projectName))
                            <input type="hidden" id="currency_id" name="currency_id" value="{{ $project->currency_id}}">
                            <x-forms.text :fieldLabel="__('modules.invoices.currency')" fieldName="project-currency" fieldId="project-currency" :fieldValue="$project->currency->currency_name" fieldReadOnly="true" />
                        @else
                            <input type="hidden" id="currency_id" name="currency_id" value="{{company()->currency_id}}">
                            <x-forms.select :fieldLabel="__('modules.invoices.currency')" fieldName="currency"
                                fieldRequired="true" fieldId="currency">
                                @foreach ($currencies as $currency)
                                    <option @selected ($currency->id == company()->currency_id)  value="{{ $currency->id }}" data-currency-name="{{$currency->currency_code}}">
                                        {{ $currency->currency_code }} ({{ $currency->currency_symbol }})
                                    </option>
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.number fieldId="exchange_rate" :fieldLabel="__('modules.currencySettings.exchangeRate')"
                        fieldName="exchange_rate" fieldRequired="true" :fieldValue="(isset($projectName) ? $project->currency->exchange_rate : ($companyCurrency ? $companyCurrency->exchange_rate : 1))" fieldReadOnly="true"
                        :fieldHelp="' '"/>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.price')" fieldName="price"
                            fieldRequired="true" fieldId="price" :fieldPlaceholder="__('placeholders.price')" />

                    </div>

                    <div class="col-md-6 col-lg-4">
                        <x-forms.datepicker fieldId="purchase_date" fieldRequired="true"
                            :fieldLabel="__('modules.expenses.purchaseDate')" fieldName="purchase_date"
                            :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="\Carbon\Carbon::today()->format(company()->date_format)" />
                    </div>

                    @if (user()->permission('add_expenses') == 'all')
                        <div class="col-md-6 col-lg-4">
                            <x-forms.label class="mt-3" fieldId="user_id" :fieldLabel="__('app.employee')">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="user_id" id="user_id"
                                    data-live-search="true" data-size="8">
                                    <option value="">--</option>
                                    @foreach ($employees as $item)
                                        <x-user-option :user="$item" />
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        </div>
                    @else
                        <input type="hidden" name="user_id" value="{{ user()->id }}">
                    @endif

                    <div class="col-md-6 col-lg-4">
                        @if(isset($projectName))
                            <input type="hidden" name="project_id" id="project_id" value="{{ $projectId }}">
                            <x-forms.text :fieldLabel="__('app.project')" fieldName="projectName" fieldId="projectName" :fieldValue="$projectName" fieldReadOnly="true" />
                        @else
                            <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                                search="true">
                                <option value="">--</option>
                                @foreach ($projects as $project)
                                    <option data-currency-id="{{ $project->currency_id }}" @selected ($projectId == $project->id) value="{{ $project->id }}">
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="mt-3" fieldId="category_id"
                            :fieldLabel="__('modules.expenses.expenseCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="expense_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addExpenseCategoryPermission == 'all' || $addExpenseCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addExpenseCategory" type="button"
                                        class="btn btn-outline-secondary border-grey"
                                        data-toggle="tooltip" data-original-title="{{__('modules.expenseCategory.addExpenseCategory') }}">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text :fieldLabel="__('modules.expenses.purchaseFrom')" fieldName="purchase_from"
                            fieldId="purchase_from" :fieldPlaceholder="__('placeholders.expense.vendor')" />
                    </div>

                    @if($linkExpensePermission == 'all')
                        <div class="col-md-4">
                            <x-forms.select fieldId="bank_account_id" :fieldLabel="__('app.menu.bankaccount')" fieldName="bank_account_id"
                                search="true">
                                <option value="">--</option>
                                @if($viewBankAccountPermission != 'none')
                                    @foreach ($bankDetails as $bankDetail)
                                        <option value="{{ $bankDetail->id }}">@if($bankDetail->type == 'bank')
                                            {{ $bankDetail->bank_name }} | @endif {{ $bankDetail->account_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </x-forms.select>
                        </div>
                    @endif
                    <input type = "hidden" name = "mention_user_ids" id = "mentionUserId" class ="mention_user_ids">

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                            </x-forms.label>
                            <div id="description"></div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="scan_content" fieldLabel="Scan Content">
                            </x-forms.label>
                            <textarea name="scan_content" id="scan_content" class="form-control" rows="50"
                                style="background-color: #fff; border: 1px solid #ced4da; border-radius: 0.25rem;"
                                placeholder="Complete OCR scanned text content will be displayed here..." readonly></textarea>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file :fieldLabel="__('app.bill')" fieldName="bill" fieldId="bill" allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>
                </div>
                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-expense-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('expenses.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script>
    $(document).ready(function() {

        quillMention(null, '#description');

        // Check for auto-fill data from OCR
        checkAndAutoFillFromOCR();

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        const dp1 = datepicker('#purchase_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#save-expense-form').click(function() {
            let note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;
            var mention_user_id = $('#description span[data-id]').map(function(){
                            return $(this).attr('data-id')
                        }).get();
            $('#mentionUserId').val(mention_user_id.join(','));
            const url = "{{ route('expenses.store') }}";
            var data = $('#save-expense-data-form').serialize();

            $.easyAjax({
                url: url,
                container: '#save-expense-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-expense-form",
                data: data,
                file: true,
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        $('#addExpenseCategory').click(function() {
            let userId = $('#user_id').val();
            const url = "{{ route('expenseCategory.create') }}?user_id="+userId;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('change', '#user_id', function(){
            let userId = $(this).val();
            let categoryId = $('#expense_category_id').val();

            const url = "{{ route('expenses.get_employee_projects') }}";
            let data = $('#save-expense-data-form').serialize();

            $.easyAjax({
                url: url,
                type: "GET",
                data: {'userId' : userId, 'categoryId' : categoryId},
                success: function(response) {
                    $('#project_id').html('<option value="">--</option>'+response.data);
                    $('#project_id').selectpicker('refresh')
                    $('#expense_category_id').html('<option value="">--</option>'+response.category);
                    $('#expense_category_id').selectpicker('refresh')
                }
            });

        });

        $('body').on('change', '#expense_category_id', function(){
            let categoryId = $(this).val();
            let userId = $('#user_id').val();

            const url = "{{ route('expenses.get_category_employees') }}";
            let data = $('#save-expense-data-form').serialize();

            $.easyAjax({
                url: url,
                type: "GET",
                data: {'categoryId' : categoryId, 'userId' : userId},
                success: function(response) {
                    $('#user_id').html('<option value="">--</option>'+response.employees);
                    $('#user_id').selectpicker('refresh')
                }
            });
        });

        init(RIGHT_MODAL);
    });

    $('body').on("change", '#currency, #project_id', function() {
        if ($('#project_id').val() != '') {
            var curId = $('#project_id option:selected').attr('data-currency-id');
            $('#currency').removeAttr('disabled');
            $('#currency').selectpicker('refresh');
            // $('#currency_id').val(curId);
            $('#currency').val(curId);
            $('#currency').prop('disabled', true);
            $('#currency').selectpicker('refresh');
        } else {
            $('#currency').prop('disabled', false);
            $('#currency').selectpicker('refresh');
        }

        var id = $('#currency').val();
        $('#currency_id').val(id);
        var currencyId = $('#currency_id').val();

        var companyCurrencyName = "{{ $companyCurrency ? $companyCurrency->currency_code : '' }}";
        var currentCurrencyName = $('#currency option:selected').attr('data-currency-name');
        var companyCurrency = '{{ $companyCurrency->id }}';

        if(currencyId == companyCurrency){
            $('#exchange_rate').prop('readonly', true);
        } else{
            $('#exchange_rate').prop('readonly', false);
        }

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: "{{ route('payments.account_list') }}",
            type: "GET",
            blockUI: true,
            data: { 'curId' : currencyId , _token: token},
            success: function(response) {
                if (response.status == 'success') {
                    $('#bank_account_id').html(response.data);
                    $('#bank_account_id').selectpicker('refresh');
                    $('#exchange_rate').val(response.exchangeRate);
                    let currencyExchange = (companyCurrencyName != currentCurrencyName) ? '( '+currentCurrencyName+' @lang('app.to') '+companyCurrencyName+' )' : '';
                    $('#exchange_rateHelp').html(currencyExchange);
                }
            }
        });
    });

    // Function to check and auto-fill form from OCR data
    function checkAndAutoFillFromOCR() {
        const autoFillData = sessionStorage.getItem('autoFillExpenseData');
        const imageData = sessionStorage.getItem('autoFillImageData');
        const originalOcrText = sessionStorage.getItem('originalOcrText');

        if (autoFillData) {
            try {
                const data = JSON.parse(autoFillData);

                // Fill basic form fields with extracted data
                if (data.item_name) {
                    $('#item_name').val(data.item_name);
                }

                if (data.price) {
                    $('#price').val(data.price);
                }

                if (data.purchase_date) {
                    const formattedDate = formatDateForInput(data.purchase_date);
                    if (formattedDate) {
                        $('#purchase_date').val(formattedDate);
                    }
                }

                if (data.purchase_from) {
                    $('#purchase_from').val(data.purchase_from);
                }

                // Fill description using Quill editor
                if (data.description) {
                    // Wait a bit for Quill to initialize
                    setTimeout(function() {
                        const quillEditor = document.getElementById('description');
                        if (quillEditor && quillEditor.children[0]) {
                            quillEditor.children[0].innerHTML = '<p>' + data.description.replace(/\n/g, '</p><p>') + '</p>';
                        }
                    }, 500);
                }

                // Fill scan content text area with original OCR text
                if (originalOcrText) {
                    $('#scan_content').val(originalOcrText);
                }

                // Intelligent field mapping for dropdowns
                intelligentFieldMapping(data);

                // Auto-upload image to bill field if available
                if (imageData) {
                    try {
                        const imgData = JSON.parse(imageData);
                        autoUploadImageToBillField(imgData);
                    } catch (e) {
                        console.error('Error parsing image data:', e);
                    }
                    // Clear image data from session storage
                    sessionStorage.removeItem('autoFillImageData');
                }

                // Clear the session storage after use
                sessionStorage.removeItem('autoFillExpenseData');
                sessionStorage.removeItem('originalOcrText');

                // Show success message
                setTimeout(function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Auto-fill Successful',
                        text: 'OCR recognized information has been automatically filled into the form. Please review and modify as necessary.',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }, 1000);

            } catch (e) {
                console.error('Error parsing auto-fill data:', e);
                sessionStorage.removeItem('autoFillExpenseData');
                sessionStorage.removeItem('autoFillImageData');
                sessionStorage.removeItem('originalOcrText');
            }
        }
    }

    // Intelligent field mapping function
    function intelligentFieldMapping(data) {
        // Map category
        if (data.category) {
            mapCategory(data.category);
        }

        // Map project
        if (data.project) {
            mapProject(data.project);
        }

        // Map employee
        if (data.employee) {
            mapEmployee(data.employee);
        }

        // Map bank account
        if (data.bank_account) {
            mapBankAccount(data.bank_account);
        }
    }

    // Function to map category based on extracted text
    function mapCategory(categoryText) {
        const categorySelect = $('#expense_category_id');
        if (!categorySelect.length) return;

        const categoryOptions = categorySelect.find('option');
        let bestMatch = null;
        let bestScore = 0;

        categoryOptions.each(function() {
            const optionText = $(this).text().toLowerCase();
            const optionValue = $(this).val();

            if (optionValue && optionText) {
                const score = calculateSimilarity(categoryText.toLowerCase(), optionText);
                if (score > bestScore && score > 0.3) { // Minimum similarity threshold
                    bestScore = score;
                    bestMatch = optionValue;
                }
            }
        });

        if (bestMatch) {
            categorySelect.val(bestMatch).trigger('change');
            console.log('Category auto-mapped:', categoryText, '→', bestMatch);
        }
    }

    // Function to map project based on extracted text
    function mapProject(projectText) {
        const projectSelect = $('#project_id');
        if (!projectSelect.length) return;

        const projectOptions = projectSelect.find('option');
        let bestMatch = null;
        let bestScore = 0;

        projectOptions.each(function() {
            const optionText = $(this).text().toLowerCase();
            const optionValue = $(this).val();

            if (optionValue && optionText) {
                const score = calculateSimilarity(projectText.toLowerCase(), optionText);
                if (score > bestScore && score > 0.3) { // Minimum similarity threshold
                    bestScore = score;
                    bestMatch = optionValue;
                }
            }
        });

        if (bestMatch) {
            projectSelect.val(bestMatch).trigger('change');
            console.log('Project auto-mapped:', projectText, '→', bestMatch);
        }
    }

    // Function to map employee based on extracted text
    function mapEmployee(employeeText) {
        const employeeSelect = $('#user_id');
        if (!employeeSelect.length) return;

        const employeeOptions = employeeSelect.find('option');
        let bestMatch = null;
        let bestScore = 0;

        employeeOptions.each(function() {
            const optionText = $(this).text().toLowerCase();
            const optionValue = $(this).val();

            if (optionValue && optionText) {
                const score = calculateSimilarity(employeeText.toLowerCase(), optionText);
                if (score > bestScore && score > 0.3) { // Minimum similarity threshold
                    bestScore = score;
                    bestMatch = optionValue;
                }
            }
        });

        if (bestMatch) {
            employeeSelect.val(bestMatch).trigger('change');
            console.log('Employee auto-mapped:', employeeText, '→', bestMatch);
        }
    }

    // Function to map bank account based on extracted text
    function mapBankAccount(bankAccountText) {
        const bankAccountSelect = $('#bank_account_id');
        if (!bankAccountSelect.length) return;

        const bankAccountOptions = bankAccountSelect.find('option');
        let bestMatch = null;
        let bestScore = 0;

        bankAccountOptions.each(function() {
            const optionText = $(this).text().toLowerCase();
            const optionValue = $(this).val();

            if (optionValue && optionText) {
                const score = calculateSimilarity(bankAccountText.toLowerCase(), optionText);
                if (score > bestScore && score > 0.3) { // Minimum similarity threshold
                    bestScore = score;
                    bestMatch = optionValue;
                }
            }
        });

        if (bestMatch) {
            bankAccountSelect.val(bestMatch).trigger('change');
            console.log('Bank Account auto-mapped:', bankAccountText, '→', bestMatch);
        }
    }

    // Function to calculate text similarity using Levenshtein distance
    function calculateSimilarity(str1, str2) {
        const len1 = str1.length;
        const len2 = str2.length;

        if (len1 === 0) return len2 === 0 ? 1 : 0;
        if (len2 === 0) return 0;

        // Check for exact match or substring match
        if (str1 === str2) return 1;
        if (str1.includes(str2) || str2.includes(str1)) return 0.8;

        // Calculate Levenshtein distance
        const matrix = Array(len1 + 1).fill().map(() => Array(len2 + 1).fill(0));

        for (let i = 0; i <= len1; i++) matrix[i][0] = i;
        for (let j = 0; j <= len2; j++) matrix[0][j] = j;

        for (let i = 1; i <= len1; i++) {
            for (let j = 1; j <= len2; j++) {
                const cost = str1[i - 1] === str2[j - 1] ? 0 : 1;
                matrix[i][j] = Math.min(
                    matrix[i - 1][j] + 1,      // deletion
                    matrix[i][j - 1] + 1,      // insertion
                    matrix[i - 1][j - 1] + cost // substitution
                );
            }
        }

        const distance = matrix[len1][len2];
        const maxLen = Math.max(len1, len2);
        return 1 - (distance / maxLen);
    }

    // Function to auto-upload image to bill field
    function autoUploadImageToBillField(imageData) {
        try {
            // Convert base64 data back to File object
            const byteCharacters = atob(imageData.data.split(',')[1]);
            const byteNumbers = new Array(byteCharacters.length);
            for (let i = 0; i < byteCharacters.length; i++) {
                byteNumbers[i] = byteCharacters.charCodeAt(i);
            }
            const byteArray = new Uint8Array(byteNumbers);
            const file = new File([byteArray], imageData.name, { type: imageData.type });

            // Get the bill file input element
            const billInput = document.getElementById('bill');
            if (billInput) {
                // Create a new FileList with our file
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                billInput.files = dataTransfer.files;

                // Trigger the dropify update if it's initialized
                if ($(billInput).hasClass('dropify-initialized')) {
                    $(billInput).dropify('destroy');
                    $(billInput).dropify({
                        defaultFile: imageData.data
                    });
                } else {
                    // Initialize dropify with the default file
                    $(billInput).dropify({
                        defaultFile: imageData.data
                    });
                }

                console.log('Image auto-uploaded to bill field successfully');
            } else {
                console.error('Bill input field not found');
            }
        } catch (e) {
            console.error('Error auto-uploading image to bill field:', e);
        }
    }

    // Function to format date for input field
    function formatDateForInput(dateString) {
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return null;
            }

            // Format as YYYY-MM-DD for date input
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        } catch (e) {
            return null;
        }
    }

    @if(isset($projectName))
        setExchangeRateHelp();
        function setExchangeRateHelp(){
            $('#exchange_rate').prop('readonly', false);
            var companyCurrencyName = "{{$companyCurrency->currency_name}}";
            var currentCurrencyName = `{{ $project->currency->currency_name }}` ;
            let currencyExchange = (companyCurrencyName != currentCurrencyName) ? '( '+currentCurrencyName+' @lang('app.to') '+currentCurrencyName+' )' : '';
            $('#exchange_rateHelp').html(currencyExchange);
        }
    @endif

</script>
