@extends('layouts.app')

@section('filter-section')

    <x-filters.filter-box>
        <!-- SEARCH BY TASK START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.filterBy')</p>
            <div class="select-status d-flex">
                <input type="hidden" id="hidden-table-url" value="{{ route('settings.custom-module-settings.index') }}">
            </div>
        </div>
        <!-- SEARCH BY TASK END -->

        <x-slot name="action">
            <!-- SEARCH BY TASK START -->
            <div class="select-box d-flex  py-2 px-lg-2 px-md-2 px-0 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.status')</p>
                <div class="select-status">
                    <select class="form-control select-picker" name="status" id="status" data-live-search="true"
                            data-size="8">
                        <option value="all">@lang('app.all')</option>
                        <option value="active">@lang('app.active')</option>
                        <option value="inactive">@lang('app.inactive')</option>
                    </select>
                </div>
            </div>
            <!-- SEARCH BY TASK END -->
        </x-slot>
    </x-filters.filter-box>

@endsection

@section('content')
    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <nav class="tabs px-4 border-bottom-grey">
                        <div class="nav" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link f-15 custom active"
                               href="{{ route('settings.custom-module-settings.index') }}?tab=custom" role="tab"
                               aria-controls="nav-ticketChannel" aria-selected="true">@lang('app.menu.customModule')
                            </a>
                        </div>
                    </nav>
                </div>
            </x-slot>

            <x-slot name="buttons">
                <div class="row">
                    <div class="col-md-12 my-2">
                        <x-forms.link-primary :link="route('settings.custom-module-settings.create')" icon="cog">
                            @lang('app.install')/@lang('app.update')
                            @lang('app.module')
                        </x-forms.link-primary>
                    </div>
                </div>
            </x-slot>

            {{-- include tabs here --}}
            @include($view)

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')
    <script>
        $("body").on("click", ".nav a", function(event) {
            event.preventDefault();

            $('.nav .nav-link').removeClass('active');
            $(this).addClass('active');

            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html)
                    }
                }
            });

        });

    </script>
@endpush