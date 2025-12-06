<div class="row">
    @forelse($modules as $module)
        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1 f-16 text-dark">{{ $module['display_name'] }}</h5>
                            <p class="card-text text-lightest f-12 mb-2">{{ $module['name'] }}</p>
                            <span class="badge badge-light f-11">v{{ $module['version'] }}</span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0" aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item openRightModal show-module-details" href="javascript:;" data-module-id="{{ $module['id'] }}">
                                    <i class="fa fa-info mr-2"></i>@lang('app.details')
                                </a>
                            </div>
                        </div>
                    </div>

                    @if($module['description'])
                        <p class="card-text text-dark-grey f-13 mb-3">{{ Str::limit($module['description'], 100) }}</p>
                    @endif

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input toggle-module-status" 
                                   id="module-{{ $module['id'] }}" 
                                   data-module-id="{{ $module['id'] }}"
                                   {{ $module['is_enabled'] ? 'checked' : '' }}>
                            <label class="custom-control-label f-14" for="module-{{ $module['id'] }}">
                                {{ $module['is_enabled'] ? __('app.enabled') : __('app.disabled') }}
                            </label>
                        </div>
                        
                        @if($module['is_enabled'])
                            <span class="badge badge-success">@lang('app.active')</span>
                        @else
                            <span class="badge badge-secondary">@lang('app.inactive')</span>
                        @endif
                    </div>

                    @if(!empty($module['keywords']))
                        <div class="mt-3">
                            @foreach(array_slice($module['keywords'], 0, 3) as $keyword)
                                <span class="badge badge-light mr-1 f-11">{{ $keyword }}</span>
                            @endforeach
                            @if(count($module['keywords']) > 3)
                                <span class="text-lightest f-11">+{{ count($module['keywords']) - 3 }} more</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <x-cards.no-record icon="layer-group" :message="__('messages.noModuleFound')" />
        </div>
    @endforelse
</div>

<script>
    $('.toggle-module-status').change(function() {
        var id = $(this).data('module-id');
        var url = "{{ route('superadmin.settings.module-switch.toggle-status') }}";
        var token = "{{ csrf_token() }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            data: {'_token': token, 'id': id},
            success: function (response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        });
    });

    $('.show-module-details').click(function() {
        var moduleId = $(this).data('module-id');
        var url = "{{ route('superadmin.settings.module-switch.show', ':id') }}";
        url = url.replace(':id', moduleId);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>