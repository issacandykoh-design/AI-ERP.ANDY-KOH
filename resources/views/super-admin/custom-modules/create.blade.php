@extends('layouts.app')

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>

            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        <a href="{{ route('settings.custom-module-settings.index') }}" class="btn btn-link p-0 mr-2" aria-label="Back">
                            <i class="fa fa-angle-left"></i>
                        </a>
                        @lang('app.install')/@lang('app.update')
                        @lang('app.module')
                    </h2>
                </div>
            </x-slot>

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">

                <x-form id="save-module-data-form" method="POST" class="ajax-form">
                    <div class="add-client bg-white rounded">
                        <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                            @lang('app.install')/@lang('app.update') @lang('app.module')</h4>
                        <div class="row p-20">
                            <div class="col-lg-12">
                                <x-forms.file allowedFileExtensions="zip" class="mr-0 mr-lg-2 mr-md-2"
                                              :fieldLabel="__('app.selectModule')" fieldName="module" fieldId="module"
                                              :fieldPlaceholder="__('placeholders.module')" fieldRequired="true"/>
                            </div>

                        </div>

                        <x-form-actions>
                            <x-forms.button-primary id="save-module-form" class="mr-3" icon="check">
                                @lang('app.save')
                            </x-forms.button-primary>
                            <x-forms.button-cancel :link="route('settings.custom-module-settings.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                        </x-form-actions>
                    </div>
                </x-form>

            </div>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>
        $('#save-module-form').click(function() {
            const url = "{{ route('settings.custom-module-settings.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-module-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-module-form",
                file: true,
                data: $('#save-module-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

    </script>
@endpush
