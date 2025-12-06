<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">{{ $module['display_name'] }} @lang('app.details')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
    </button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.name')</label>
                                <p class="f-15 text-dark">{{ $module['display_name'] }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.version')</label>
                                <p class="f-15 text-dark">{{ $module['version'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.status')</label>
                                <p class="f-15">
                                    @if($module['is_enabled'])
                                        <span class="badge badge-success">@lang('app.enabled')</span>
                                    @else
                                        <span class="badge badge-danger">@lang('app.disabled')</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.size')</label>
                                <p class="f-15 text-dark">{{ $module['size'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($module['description'])
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.description')</label>
                                <p class="f-15 text-dark">{{ $module['description'] }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($module['keywords']) && !empty($module['keywords']))
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.keywords')</label>
                                <div>
                                    @foreach($module['keywords'] as $keyword)
                                        <span class="badge badge-light mr-1 mb-1">{{ $keyword }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($module['providers']) && !empty($module['providers']))
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.serviceProviders')</label>
                                <div>
                                    @foreach($module['providers'] as $provider)
                                        <span class="badge badge-secondary mr-1 mb-1 f-12">{{ $provider }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-1">@lang('app.path')</label>
                                <p class="f-13 text-lightest">{{ $module['path'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    @if(user()->permission('manage_superadmin_module_switch') == 'all')
        <x-forms.button-primary class="toggle-module-status-modal" data-module-id="{{ $module['id'] }}">
            @if($module['is_enabled'])
                <i class="fa fa-power-off mr-1"></i>@lang('app.disable')
            @else
                <i class="fa fa-power-off mr-1"></i>@lang('app.enable')
            @endif
        </x-forms.button-primary>
    @endif
</div>

<script>
    $('.toggle-module-status-modal').click(function() {
        var id = $(this).data('module-id');
        var url = "{{ route('superadmin.settings.module-switch.toggle-status') }}";
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token': token, 'id': id},
            success: function (response) {
                if (response.status == "success") {
                    $(MODAL_LG).modal('hide');
                    window.location.reload();
                }
            }
        });
    });
</script>