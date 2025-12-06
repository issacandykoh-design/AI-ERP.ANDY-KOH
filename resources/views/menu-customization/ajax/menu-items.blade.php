<div class="menu-customization-wrapper w-100">
    <div class="p-3 border-bottom bg-light">
        <small class="text-muted mb-0">
            <i class="fa fa-info-circle mr-1"></i>
            @lang('app.menuCustomizationInfo')
        </small>
        <span class="badge badge-success ml-2" id="save-status" style="display: none;">
            <i class="fa fa-check"></i> @lang('app.saved')
        </span>
    </div>

    <div class="p-0">
        <table class="table table-hover table-sm mb-0 menu-customization-table">
            <thead class="thead-light">
                <tr>
                    <th class="py-2" style="width: 50px;">@lang('app.menuOrder')</th>
                    <th class="py-2" style="width: 50px;">@lang('app.icon')</th>
                    <th class="py-2">@lang('app.defaultName')</th>
                    <th class="py-2">@lang('app.customName')</th>
                    <th class="py-2 text-center" style="width: 60px;">@lang('app.action')</th>
                </tr>
            </thead>
            <tbody id="menu-items-list">
                @foreach($menuItems as $item)
                    <tr class="menu-item-row" 
                        data-menu-key="{{ $item['key'] }}"
                        data-menu-order="{{ $item['menu_order'] }}">
                        <td class="py-2">
                            <div class="drag-handle cursor-move d-flex align-items-center" style="cursor: move;">
                                <i class="fa fa-grip-vertical text-lightest"></i>
                                <span class="ml-1 small">{{ $item['menu_order'] }}</span>
                            </div>
                        </td>
                        <td class="py-2">
                            <x-icon :name="$item['icon']" size="18" />
                        </td>
                        <td class="py-2">
                            <div class="text-truncate" title="{{ $item['default_name'] }}">
                                <strong class="small">{{ $item['default_name'] }}</strong>
                            </div>
                        </td>
                        <td class="py-2">
                            <input type="text" 
                                   class="form-control form-control-sm custom-menu-name" 
                                   data-menu-key="{{ $item['key'] }}"
                                   value="{{ $item['custom_name'] ?? '' }}"
                                   placeholder="{{ $item['default_name'] }}">
                        </td>
                        <td class="py-2 text-center">
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary reset-menu-name"
                                    data-menu-key="{{ $item['key'] }}"
                                    data-default-name="{{ $item['default_name'] }}"
                                    title="@lang('app.reset')">
                                <i class="fa fa-undo"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="p-2 border-top bg-light">
        <small class="text-muted">
            <i class="fa fa-exclamation-triangle mr-1"></i>
            <strong>@lang('app.note'):</strong> @lang('app.menuCustomizationNote')
        </small>
    </div>
</div>

<style>
    /* Override the flex wrapper from setting-card component - CRITICAL FIX */
    #nav-email > .d-flex {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .menu-customization-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    
    .menu-customization-table {
        width: 100% !important;
        table-layout: fixed !important;
        margin: 0 !important;
    }
    
    .menu-customization-table th:nth-child(1),
    .menu-customization-table td:nth-child(1) {
        width: 60px;
    }
    
    .menu-customization-table th:nth-child(2),
    .menu-customization-table td:nth-child(2) {
        width: 50px;
    }
    
    .menu-customization-table th:nth-child(3),
    .menu-customization-table td:nth-child(3) {
        width: 20%;
        min-width: 150px;
        max-width: 200px;
    }
    
    .menu-customization-table th:nth-child(4),
    .menu-customization-table td:nth-child(4) {
        width: 40%;
        min-width: 250px;
    }
    
    .menu-customization-table th:nth-child(5),
    .menu-customization-table td:nth-child(5) {
        width: 60px;
    }
    
    .drag-handle {
        cursor: move;
        user-select: none;
    }
    
    .menu-item-row {
        transition: background-color 0.15s ease;
        will-change: background-color;
    }
    
    .menu-item-row:hover {
        background-color: #f8f9fa;
    }
    
    .menu-item-row td {
        position: relative;
        overflow: hidden;
    }
    
    .opacity-50 {
        opacity: 0.5;
    }
    
    .sortable-ghost {
        opacity: 0.4;
        background: #f0f0f0;
    }
    
    .table-sm td, .table-sm th {
        padding: 0.5rem;
        vertical-align: middle;
    }
    
    .menu-customization-table td:nth-child(3) {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .menu-customization-table td:nth-child(4) {
        overflow: visible;
        white-space: normal;
    }
    
    .menu-customization-table td:nth-child(3) .text-truncate {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 100%;
    }
    
    .custom-menu-name {
        width: 100%;
        box-sizing: border-box;
    }
</style>

