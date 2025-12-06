@extends('layouts.app')

@push('styles')
@endpush

@section('content')
    <div class="w-100 d-flex">
        <x-super-admin.front-setting-sidebar :activeMenu="$activeSettingMenu"/>
        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="f-21 font-weight-normal text-capitalize border-bottom-grey mb-0 p-20">@lang($pageTitle)</h2>
                </div>
            </x-slot>
            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="form-group">
                            <x-forms.text fieldId="ai_api_key" fieldLabel="OpenRouter API Key"
                                          fieldName="ai_api_key" :fieldValue="$aiApiKey"
                                          fieldPlaceholder="sk-or-v1-..." fieldRequired="true" />
                            <div class="mt-3">
                                <x-forms.button-primary id="save-api-key" class="mr-2" icon="check">Save</x-forms.button-primary>
                                <x-forms.button-secondary id="test-connection" icon="plug">Test</x-forms.button-secondary>
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <x-forms.select fieldId="llm_model" fieldLabel="Select LLM Model"
                                            fieldName="llm_model" fieldRequired="true">
                                @if(isset($availableModels) && count($availableModels) > 0)
                                    @foreach($availableModels as $model)
                                        <option value="{{ $model['id'] }}" @if($currentLlm == $model['id']) selected @endif>
                                            {{ $model['name'] ?? $model['id'] }}
                                            @if(isset($model['context_length']))
                                                ({{ number_format($model['context_length']) }} context)
                                            @endif
                                        </option>
                                    @endforeach
                                @else
                                    <option value="openai/gpt-3.5-turbo" @if($currentLlm == 'openai/gpt-3.5-turbo') selected @endif>GPT-3.5 Turbo (OpenAI)</option>
                                    <option value="openai/gpt-4-turbo" @if($currentLlm == 'openai/gpt-4-turbo') selected @endif>GPT-4 Turbo (OpenAI)</option>
                                    <option value="anthropic/claude-3-sonnet" @if($currentLlm == 'anthropic/claude-3-sonnet') selected @endif>Claude 3 Sonnet (Anthropic)</option>
                                    <option value="google/gemini-pro" @if($currentLlm == 'google/gemini-pro') selected @endif>Gemini Pro (Google)</option>
                                @endif
                            </x-forms.select>
                            <div class="mt-3">
                                <x-forms.button-primary id="switch-llm" icon="exchange-alt" class="mr-2">Save Model</x-forms.button-primary>
                                <x-forms.button-secondary id="refresh-models" icon="sync-alt">Refresh</x-forms.button-secondary>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="row" id="token-usage-stats">
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="bg-additional-grey rounded p-3 text-center">
                                        <h3 id="total-tokens">{{ number_format($tokenUsage['total_tokens_used']) }}</h3>
                                        <div class="f-12 text-dark-grey">Total Tokens Used</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="bg-additional-grey rounded p-3 text-center">
                                        <h3 id="tokens-remaining">{{ number_format($tokenUsage['tokens_remaining']) }}</h3>
                                        <div class="f-12 text-dark-grey">Tokens Remaining</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="bg-additional-grey rounded p-3 text-center">
                                        <h3 id="monthly-limit">{{ number_format($tokenUsage['monthly_limit']) }}</h3>
                                        <div class="f-12 text-dark-grey">Monthly Limit</div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 mb-3">
                                    <div class="bg-additional-grey rounded p-3 text-center">
                                        <h3 id="current-month-usage">{{ number_format($tokenUsage['current_month_usage']) }}</h3>
                                        <div class="f-12 text-dark-grey">Current Month Usage</div>
                                    </div>
                                </div>
                            </div>
                            @php $usagePercentage = ($tokenUsage['current_month_usage'] / $tokenUsage['monthly_limit']) * 100; @endphp
                            <div class="progress">
                                @php $progressClass = $usagePercentage > 80 ? 'bg-danger' : ($usagePercentage > 60 ? 'bg-warning' : 'bg-success'); @endphp
                                <div class="progress-bar {{ $progressClass }}" role="progressbar" style="width: {{ $usagePercentage }}%;" aria-valuenow="{{ $usagePercentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="f-12 text-dark-grey mt-2">Last Updated: <span id="last-updated">{{ $tokenUsage['last_updated'] }}</span></div>
                            <div class="row mt-4">
                                <div class="col-lg-6">
                                    <div class="bg-additional-grey rounded p-3">
                                        <div class="f-14 text-dark-grey mb-2">By Feature</div>
                                        <ul class="f-12 text-dark-grey pl-3 mb-0" id="by-feature"></ul>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="bg-additional-grey rounded p-3">
                                        <div class="f-14 text-dark-grey mb-2">By Model</div>
                                        <ul class="f-12 text-dark-grey pl-3 mb-0" id="by-model"></ul>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-additional-grey rounded p-3 mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="f-14 text-dark-grey">Recent AI Requests</div>
                                    <x-forms.button-secondary id="refresh-token-usage" icon="sync-alt" class="btn-sm">Refresh</x-forms.button-secondary>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-bordered f-12 mt-2 mb-0">
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <th>Feature</th>
                                                <th>Model</th>
                                                <th>Prompt</th>
                                                <th>Completion</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="usage-events"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="bg-additional-grey rounded p-4">
                            <div class="f-14 text-dark-grey mb-2">Important Notes</div>
                            <ul class="f-12 text-dark-grey pl-3 mb-0">
                                <li>OpenRouter API keys are stored in .env</li>
                                <li>Test connection after updating</li>
                                <li>All AI services use the selected model</li>
                                <li>Usage updates periodically from OpenRouter</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </x-setting-card>
    </div>
@endsection

@push('scripts')
    <script>
        // Save API Key
        $('#save-api-key').click(function() {
            const apiKey = $('#ai_api_key').val();
            const $btn = $(this);
            const originalHtml = $btn.html();

            if (!apiKey || apiKey.length < 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a valid API key (minimum 10 characters)'
                });
                return;
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');

            $.easyAjax({
                url: "{{ route('superadmin.settings.ai-settings.update_api_key') }}",
                type: "POST",
                data: {
                    api_key: apiKey,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Switch LLM Model
        $('#switch-llm').click(function() {
            const llmModel = $('#llm_model').val();
            const $btn = $(this);
            const originalHtml = $btn.html();

            if (!llmModel) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please select a model'
                });
                return;
            }

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Saving...');

            $.easyAjax({
                url: "{{ route('superadmin.settings.ai-settings.switch_llm') }}",
                type: "POST",
                data: {
                    llm_model: llmModel,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        // Refresh Models List
        $('#refresh-models').click(function() {
            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.easyAjax({
                url: "{{ route('superadmin.settings.ai-settings.index') }}",
                type: "GET",
                success: function(response) {
                    if (response.status === 'success') {
                        // Reload the page to get updated models
                        location.reload();
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to refresh models list'
                    });
                }
            });
        });

        // Test Connection
        $('#test-connection').click(function() {
            const apiKey = $('#ai_api_key').val();

            if (!apiKey || apiKey.length < 10) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Please enter a valid OpenRouter API key'
                });
                return;
            }

            const $btn = $(this);
            const originalHtml = $btn.html();
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Testing...');

            $.easyAjax({
                url: "{{ route('superadmin.settings.ai-settings.test_connection') }}",
                type: "POST",
                data: {
                    api_key: apiKey,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    $btn.prop('disabled', false).html(originalHtml);
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message
                        });
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).html(originalHtml);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Connection test failed'
                    });
                }
            });
        });

        // Refresh Token Usage
        $('#refresh-token-usage').click(function() {
            $.easyAjax({
                url: "{{ route('superadmin.settings.ai-settings.refresh_token_usage') }}",
                type: "GET",
                success: function(response) {
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#total-tokens').text(new Intl.NumberFormat().format(data.total_tokens_used));
                        $('#tokens-remaining').text(new Intl.NumberFormat().format(data.tokens_remaining));
                        $('#monthly-limit').text(new Intl.NumberFormat().format(data.monthly_limit));
                        $('#current-month-usage').text(new Intl.NumberFormat().format(data.current_month_usage));
                        $('#last-updated').text(data.last_updated);

                        const usagePercentage = (data.current_month_usage / data.monthly_limit) * 100;
                        const progressBar = $('.progress-bar');
                        progressBar.css('width', usagePercentage + '%');
                        progressBar.attr('aria-valuenow', usagePercentage);
                        progressBar.removeClass('bg-success bg-warning bg-danger');
                        if (usagePercentage > 80) progressBar.addClass('bg-danger');
                        else if (usagePercentage > 60) progressBar.addClass('bg-warning');
                        else progressBar.addClass('bg-success');

                        if (data.by_feature) {
                            const bf = Object.entries(data.by_feature);
                            $('#by-feature').html(bf.map(([k,v]) => `<li>${k}: ${new Intl.NumberFormat().format(v)} tokens</li>`).join(''));
                        }
                        if (data.by_model) {
                            const bm = Object.entries(data.by_model);
                            $('#by-model').html(bm.map(([k,v]) => `<li>${k}: ${new Intl.NumberFormat().format(v)} tokens</li>`).join(''));
                        }
                        if (data.events) {
                            const rows = data.events.map(ev => `
                                <tr>
                                    <td>${ev.ts}</td>
                                    <td>${ev.feature}</td>
                                    <td>${ev.model}</td>
                                    <td>${new Intl.NumberFormat().format(ev.prompt_tokens)}</td>
                                    <td>${new Intl.NumberFormat().format(ev.completion_tokens)}</td>
                                    <td>${new Intl.NumberFormat().format(ev.total_tokens)}</td>
                                </tr>
                            `).join('');
                            $('#usage-events').html(rows);
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'Token usage statistics refreshed successfully',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                }
            });
        });
    </script>
@endpush
