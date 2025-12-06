@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <!-- SEARCH BY TASK START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.search')</p>
            <div class="select-status d-flex">
                <input type="text" class="form-control height-35 f-14" id="search-text-field" placeholder="@lang('app.startTyping')">
            </div>
        </div>
        <!-- SEARCH BY TASK END -->

        <!-- STATUS START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
            <div class="select-status">
                <select class="form-control select-picker" name="status" id="status" data-live-search="true" data-size="8">
                    <option value="all">@lang('app.all')</option>
                    <option value="enabled">@lang('app.enabled')</option>
                    <option value="disabled">@lang('app.disabled')</option>
                </select>
            </div>
        </div>
        <!-- STATUS END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-2 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->

    </x-filters.filter-box>

@endsection

@php
$addModuleSwitchPermission = user()->permission('manage_superadmin_module_switch');
@endphp

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <!-- Add Task Export Buttons Start -->
        <div class="d-flex justify-content-between action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if ($addModuleSwitchPermission == 'all')
                    <x-forms.link-primary :link="'javascript:;'" class="mr-3 openRightModal float-left" data-toggle="tooltip" data-original-title="@lang('app.enableAllModules')" onclick="enableAllModules()">
                        <i class="fa fa-check mr-1"></i>@lang('app.enableAll')
                    </x-forms.link-primary>
                    <x-forms.link-secondary :link="'javascript:;'" class="mr-3 openRightModal float-left" data-toggle="tooltip" data-original-title="@lang('app.disableAllModules')" onclick="disableAllModules()">
                        <i class="fa fa-times mr-1"></i>@lang('app.disableAll')
                    </x-forms.link-secondary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="enable">@lang('app.enable')</option>
                        <option value="disable">@lang('app.disable')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>
        <!-- Add Task Export Buttons End -->

        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">

            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}

        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#module-switch-table').on('preXhr.dt', function (e, settings, data) {
            var searchText = $('#search-text-field').val();
            var status = $('#status').val();
            data['searchText'] = searchText;
            data['status'] = status;
        });

        const showTable = () => {
            window.LaravelDataTables["module-switch-table"].draw(false);
        }

        $('#search-text-field, #status').on('change keyup', function () {
            if ($('#search-text-field').val() != "" || $('#status').val() != "all") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            } else {
                $('#reset-filters').addClass('d-none');
                showTable();
            }
        });

        $('#reset-filters').click(function () {
            $('#search-text-field').val('');
            $('#status').val('all').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        function enableAllModules() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.enableAllModulesConfirmation')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmEnable')",
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
                    var url = "{{ route('superadmin.settings.module-switch.bulk-toggle') }}";

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, 'action': 'enable_all'},
                        success: function (response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        }

        function disableAllModules() {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.disableAllModulesConfirmation')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDisable')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-danger mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('superadmin.settings.module-switch.bulk-toggle') }}";

                    var token = "{{ csrf_token() }}";

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: {'_token': token, 'action': 'disable_all'},
                        success: function (response) {
                            if (response.status == "success") {
                                showTable();
                            }
                        }
                    });
                }
            });
        }

        $('body').on('click', '.toggle-module-status', function () {
            var id = $(this).data('module-id');
            var url = "{{ route('superadmin.settings.module-switch.toggle-status') }}";

            var token = "{{ csrf_token() }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                data: {'_token': token, 'id': id},
                success: function (response) {
                    if (response.status == "success") {
                        showTable();
                    }
                }
            });
        });

        $('body').on('click', '.show-module-details', function () {
            var moduleId = $(this).data('module-id');
            var url = "{{ route('superadmin.settings.module-switch.show', ':id') }}";
            url = url.replace(':id', moduleId);

            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

    </script>
@endpush