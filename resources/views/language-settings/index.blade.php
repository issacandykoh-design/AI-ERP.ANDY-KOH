@extends('layouts.app')

@push('styles')

@endpush

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        @include('sections.setting-sidebar')

        <x-setting-card>

            <x-slot name="buttons">
                <div class="row">
                    <div class="mb-2 col-md-12">
                        <x-forms.button-primary icon="plus" id="add-language"
                                                class="mb-2 mr-2"> @lang('app.addNewLanguage')
                        </x-forms.button-primary>
                        <x-forms.button-secondary icon="cog" id="translations"
                                                  class="mb-2 mr-2"> @lang('modules.languageSettings.translate')
                        </x-forms.button-secondary>
                        <x-forms.button-primary icon="robot" id="aiTranslate"
                                                  class="mb-2 mr-2" title="Translate all languages using AI" :disabled="isset($aiConfigured) && !$aiConfigured"> 
                            AI Translate All
                        </x-forms.button-primary>
                        <x-forms.button-secondary icon="cog" id="autoTranslate"
                                                  class="mb-2"> @lang('modules.languageSettings.autoTranslate')
                        </x-forms.button-secondary>
                        
                    </div>
                </div>
            </x-slot>

            <x-slot name="header">

                <div class="s-b-n-header" id="tabs">

                    <h2 class="p-20 mb-0 f-21 font-weight-normal  border-bottom-grey">
                        @lang($pageTitle)
                    </h2>
                </div>
            </x-slot>


            <!-- LEAVE SETTING START -->
            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100">

                <div class="mt-2 mb-2 alert alert-primary">

                    <div><strong>Note:</strong>
                        {{__('messages.languageEnabledAlertMessage')}}
                    </div>
                </div>

                <x-table class="table table-sm-responsive">
                    <x-slot name="thead">
                        <th>@lang('app.languageName')</th>
                        <th>@lang('app.languageCode')</th>
                        <th width="10%">@lang('app.rtlStatus')</th>
                        <th>@lang('app.status')</th>
                        <th width="50%" class="text-right">@lang('app.action')</th>
                    </x-slot>

                    @if($languages && $languages->count())
                        @foreach($languages as $language)
                            <tr id="languageRow{{ $language->id }}" class="{{ (!user()->dark_theme && companyOrGlobalSetting()->locale === $language->language_code) ? 'bg-additional-grey' : '' }}">
                                <td>{{ $language->language_name }}</td>
                                <td>{{ $language->language_code }}</td>
                                <td>
                                    <span class="badge badge-{{ $language->is_rtl == 1 ? 'success' : 'secondary' }}">
                                        @lang('app.' . ($language->is_rtl == 1 ? 'yes' : 'no'))
                                    </span>
                                </td>
                                <td>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" {{ $language->status == 'enabled' ? 'checked' : '' }}
                                               class="custom-control-input change-language-setting"
                                               id="{{ $language->id }}" {{ companyOrGlobalSetting()->locale === $language->language_code ? 'disabled' : '' }}>
                                        <label class="cursor-pointer custom-control-label f-14"
                                               for="{{ $language->id }}"></label>
                                    </div>
                                </td>

                                <td class='text-right'>
                                    <button type="button" class="p-2 rounded btn btn-outline-primary f-14 edit-language"
                                            data-language-id="{{ $language->id }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button type="button" class="p-2 rounded btn btn-outline-danger f-14 delete-language"
                                            data-language-id="{{ $language->id }}" {{ companyOrGlobalSetting()->locale === $language->language_code ? 'disabled' : '' }}>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                    <button type="button" onclick="window.location.href='{{ route('app-settings.index') }}'"
                                        class="p-2 rounded btn btn-outline-secondary f-14"
                                        data-toggle="popover" data-placement="top"
                                        data-content="{{ companyOrGlobalSetting()->locale == $language->language_code 
                                            ? __('messages.defaultLanguageCantChange',["appsettings"=> "<a href='".route('app-settings.index')."'>".__('app.menu.appSettings')."</a>"]) 
                                            : __('messages.defaultEnLanguageCantChange') }}"
                                        data-html="true" data-trigger="hover">
                                        &nbsp;<i class="fas fa-question-circle"></i>&nbsp;
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <x-cards.no-record-found-list colspan="4"/>
                    @endif

                </x-table>

            </div>
            <!-- LEAVE SETTING END -->

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->

@endsection

    @push('scripts')
    
    <script>
        const aiConfigured = {{ isset($aiConfigured) && $aiConfigured ? 'true' : 'false' }};

        $('body').on('click', '#translations', function () {
            const url = "{{ url('/translations') }}";

            window.open(url, '_blank');
        });


        $('body').on('click', '#add-language', function () {
            var url = "{{ route('language-settings.create')}}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').off('click', '.edit-language').on('click', '.edit-language', function () {
            var id = $(this).data('language-id');
            var url = "{{ route('language-settings.edit', ':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').off('click', '.delete-language').on('click', '.delete-language', function () {
            var id = $(this).data('language-id');
            Swal.fire({
                title: '{{ __('app.areYouSure') }}',
                text: '{{ __('messages.deleteLangConfirm') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('app.delete') }}',
                cancelButtonText: '{{ __('app.cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.easyAjax({
                        url: "{{ route('language-settings.destroy', ':id') }}".replace(':id', id),
                        type: "POST",
                        data: {_method: 'DELETE', _token: '{{ csrf_token() }}'},
                        blockUI: true,
                        success: function (response) {
                            if (response.status === 'success') {
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });

        $('body').on('click', '#autoTranslate', function () {
            var url = "{{ route('language_settings.auto_translate')}}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        // AI Translate All Languages
        $('body').off('click', '#aiTranslate').on('click', '#aiTranslate', function () {
            if (!aiConfigured) {
                Swal.fire({
                    icon: 'warning',
                    title: 'AI Not Configured',
                    text: 'Set OpenRouter API key in AI Settings.'
                }).then(() => {
                    window.location.href = "{{ route('superadmin.settings.ai-settings.index') }}";
                });
                return;
            }
            Swal.fire({
                title: 'AI Translate All Languages',
                html: `
                    <p>This will translate all missing translations for all languages using AI.</p>
                    <p class="text-warning"><strong>Note:</strong> This may take several minutes and will use your OpenRouter API credits.</p>
                    <div class="form-group mt-3">
                        <label>Source Language:</label>
                        <select id="sourceLanguage" class="form-control">
                            <option value="en">English (en)</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Start Translation',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const sourceLang = document.getElementById('sourceLanguage').value;
                    return {
                        source_language: sourceLang
                    };
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    translateAllLanguages(result.value.source_language);
                }
            });
        });

        // AI Translate Single Language
        $('body').off('click', '.ai-translate-language').on('click', '.ai-translate-language', function () {
            if (!aiConfigured) {
                Swal.fire({
                    icon: 'warning',
                    title: 'AI Not Configured',
                    text: 'Set OpenRouter API key in AI Settings.'
                }).then(() => {
                    window.location.href = "{{ route('superadmin.settings.ai-settings.index') }}";
                });
                return;
            }
            const languageId = $(this).data('language-id');
            const languageName = $(this).data('language-name');
            
            Swal.fire({
                title: `AI Translate: ${languageName}`,
                html: `
                    <p>This will translate all missing translations for <strong>${languageName}</strong> using AI.</p>
                    <p class="text-warning"><strong>Note:</strong> This may take a few minutes and will use your OpenRouter API credits.</p>
                    <div class="form-group mt-3">
                        <label>Source Language:</label>
                        <select id="sourceLanguage" class="form-control">
                            <option value="en">English (en)</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Start Translation',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#3085d6',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    const sourceLang = document.getElementById('sourceLanguage').value;
                    return {
                        target_language_id: languageId,
                        source_language: sourceLang
                    };
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    translateLanguage(result.value);
                }
            });
        });

        function translateLanguage(data) {
            $.easyAjax({
                url: "{{ route('language_settings.ai_translate') }}",
                type: "POST",
                blockUI: true,
                data: {
                    ...data,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Translation Complete!',
                            text: response.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Translation Failed',
                            text: response.message || 'An error occurred during translation.'
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Translation Failed',
                        text: xhr.responseJSON?.message || 'An error occurred during translation.'
                    });
                }
            });
        }

        function translateAllLanguages(sourceLang) {
            // Get all non-English languages
            const languages = {!! $languages->where('language_code', '!=', 'en')->pluck('id', 'language_name')->toJson() !!};
            
            let currentIndex = 0;
            const languageEntries = Object.entries(languages);
            
            function translateNext() {
                if (currentIndex >= languageEntries.length) {
                    Swal.fire({
                        icon: 'success',
                        title: 'All Translations Complete!',
                        text: 'All languages have been translated.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                    return;
                }
                
                const [languageName, languageId] = languageEntries[currentIndex];
                
                Swal.fire({
                    title: `Translating: ${languageName}`,
                    html: `Progress: ${currentIndex + 1} of ${languageEntries.length}<br><br><div class="spinner-border" role="status"></div>`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.easyAjax({
                    url: "{{ route('language_settings.ai_translate') }}",
                    type: "POST",
                    blockUI: false,
                    data: {
                        target_language_id: languageId,
                        source_language: sourceLang,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function (response) {
                        currentIndex++;
                        translateNext();
                    },
                    error: function () {
                        currentIndex++;
                        translateNext();
                    }
                });
            }
            
            translateNext();
        }

        $('body').on('click', '.edit-language', function () {
            var id = $(this).data('language-id');
            var url = "{{ route('language-settings.edit',':id') }}";
            url = url.replace(':id', id);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.change-language-setting').change(function () {
            var id = this.id;

            if ($(this).is(':checked'))
                var status = 'enabled';
            else
                var status = 'disabled';

            var url = "{{route('language-settings.update', ':id')}}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "POST",
                blockUI: true,
                data: {'id': id, 'status': status, '_method': 'PUT', '_token': '{{ csrf_token() }}'}
            })
        });

        $('body').on('click', '.delete-language', function () {
            var id = $(this).data('language-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.deleteField')",
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

                    var url = "{{ route('language-settings.destroy',':id') }}";
                    url = url.replace(':id', id);

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, '_method': 'DELETE'},
                        blockUI: true,
                        success: function (response) {
                            if (response.status == "success") {
                                $.unblockUI();
                                $('#languageRow' + id).fadeOut();
                            }
                        }
                    });
                }
            });
        });

    </script>
    
@endpush
