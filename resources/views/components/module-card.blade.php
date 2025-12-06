@php
    $moduleName = $module['name'] ?? '';
    $moduleDisplayName = $module['display_name'] ?? $moduleName;
    $moduleDescription = $module['description'] ?? '';
    $moduleVersion = $module['version'] ?? '';
    $moduleStatus = $module['status'] ?? false;
    $moduleEnabled = $moduleStatus === true || $moduleStatus === 'enabled';
@endphp

<div class="card border-grey h-100">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div class="flex-grow-1">
                <h5 class="card-title f-16 text-dark mb-1">{{ $moduleDisplayName }}</h5>
                @if($moduleVersion)
                    <small class="text-muted">v{{ $moduleVersion }}</small>
                @endif
            </div>
            <div class="custom-control custom-switch">
                <input type="checkbox" 
                       class="custom-control-input change-module-status" 
                       id="module-{{ $moduleName }}"
                       data-module-name="{{ $moduleName }}"
                       @if($moduleEnabled) checked @endif>
                <label class="custom-control-label" for="module-{{ $moduleName }}"></label>
            </div>
        </div>
        
        @if($moduleDescription)
            <p class="card-text text-lightest f-13 mb-3">{{ $moduleDescription }}</p>
        @endif
        
        <div class="d-flex justify-content-between align-items-center">
            <span class="badge badge-{{ $moduleEnabled ? 'success' : 'secondary' }}">
                {{ $moduleEnabled ? __('app.active') : __('app.inactive') }}
            </span>
            
            @if($moduleEnabled)
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" 
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-cog"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item update-module" href="#" data-module-name="{{ $moduleName }}">
                            <i class="fa fa-sync mr-2"></i>@lang('app.update')
                        </a>
                        <a class="dropdown-item uninstall-module" href="#" data-module-name="{{ $moduleName }}">
                            <i class="fa fa-trash mr-2"></i>@lang('app.uninstall')
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>