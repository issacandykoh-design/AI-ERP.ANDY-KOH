@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        {{-- SAAS --}}
        @if(user()->is_superadmin)
            <x-super-admin.setting-sidebar :activeMenu="$activeSettingMenu"/>
        @else
            <x-setting-sidebar :activeMenu="$activeSettingMenu"/>
        @endif

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">@lang('app.menu.menuCustomization')</h4>
                        <p class="text-muted mb-0 small">@lang('app.menuCustomizationDescription')</p>
                    </div>
                    <div>
                        @if(user()->is_superadmin)
                        <button type="button" id="apply-global-default-all" class="btn btn-sm btn-outline-primary mr-2">Apply Global To All Companies</button>
                        @endif
                        <button type="button" id="reset-all-menu" class="btn btn-sm btn-outline-danger">@lang('app.reset')</button>
                    </div>
                </div>
            </x-slot>

            {{-- include menu items here --}}
            @include($view ?? 'menu-customization.ajax.menu-items')

        </x-setting-card>

        <div id="global-reset-overlay" class="reset-overlay d-none" aria-live="polite" aria-busy="true" aria-modal="true" role="dialog">
            <div class="reset-overlay-content text-center">
                <div class="mb-2"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
                <div class="progress" style="height:8px;">
                    <div id="reset-progress-bar" class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" style="width:0%"></div>
                </div>
                <div id="reset-progress-text" class="mt-2 small">0% • 5s</div>
                <button type="button" class="btn btn-sm btn-secondary mt-3" id="reset-cancel">@lang('app.cancel')</button>
            </div>
        </div>

    </div>
    <!-- SETTINGS END -->

@endsection

@push('styles')
    <style>
        /* Remove all spacing from parent containers */
        #nav-email {
            padding: 0 !important;
            margin: 0 !important;
        }
        
        #nav-email > .d-flex {
            display: block !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            justify-content: flex-start !important;
        }
        
        .s-b-n-content {
            padding: 0 !important;
        }
        
        .tab-content {
            padding: 0 !important;
        }

        .reset-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 1050;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-overlay-content {
            background: #ffffff;
            border-radius: 8px;
            padding: 16px 20px;
            min-width: 280px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        $(document).ready(function() {
            let saveTimeout;
            let isSaving = false;

            // Function to save all menu customizations
            function saveAllMenuCustomizations() {
                // Don't prevent saves if already saving - just queue it
                if (isSaving) {
                    console.log('Save already in progress, will retry...');
                    setTimeout(saveAllMenuCustomizations, 500);
                    return;
                }

                isSaving = true;
                const $status = $('#save-status');
                $status.removeClass('badge-success badge-danger').addClass('badge-warning')
                    .html('<i class="fa fa-spinner fa-spin"></i> @lang("app.saving")...')
                    .show();

                // Collect all menu items data
                const menuItems = {};
                $('#menu-items-list .menu-item-row').each(function(index) {
                    const menuKey = $(this).data('menu-key');
                    if (!menuKey) {
                        console.warn('Menu item at index ' + index + ' has no menu-key');
                        return; // Skip items without menu-key
                    }
                    const customName = $(this).find('.custom-menu-name').val();
                    const menuOrder = index + 1; // Use current DOM order

                    menuItems[menuKey] = {
                        custom_name: customName || null,
                        menu_order: menuOrder
                    };
                });
                
                console.log('Saving menu items:', menuItems);

                // Save all at once
                $.easyAjax({
                    url: "{{ route('menu-customization.save-all') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        menu_items: menuItems
                    },
                    success: function(response) {
                        isSaving = false;
                        if (response.status === 'success') {
                            $status.removeClass('badge-warning').addClass('badge-success')
                                .html('<i class="fa fa-check"></i> @lang("app.saved")');
                            // Hide status after 2 seconds
                            setTimeout(function() {
                                $status.fadeOut();
                            }, 2000);
                            
                            // Update sidebar menu items with new order and names immediately
                            setTimeout(function() {
                                const $sidebar = $('.custom-menu-list');
                                if ($sidebar.length) {
                                    // Update data-menu-order and custom names from saved data
                                    $('#menu-items-list .menu-item-row').each(function(index) {
                                        const menuKey = $(this).data('menu-key');
                                        const menuOrder = index + 1;
                                        const customName = $(this).find('.custom-menu-name').val();
                                        
                                        if (menuKey) {
                                            // Find corresponding sidebar menu item
                                            const $sidebarItem = $sidebar.find('li[data-menu-key="' + menuKey + '"]');
                                            if ($sidebarItem.length) {
                                                // Update order
                                                $sidebarItem.attr('data-menu-order', menuOrder);
                                                $sidebarItem.css('order', menuOrder);
                                                
                                                // Update custom name (or reset to default if empty)
                                                const $nameSpan = $sidebarItem.find('a .pl-3, a span.pl-3');
                                                if ($nameSpan.length) {
                                                    if (customName && customName.trim()) {
                                                        $nameSpan.text(customName);
                                                    } else {
                                                        // Reset to default name if custom name is empty
                                                        const $row = $('#menu-items-list .menu-item-row[data-menu-key="' + menuKey + '"]');
                                                        const defaultName = $row.find('.reset-menu-name').data('default-name') || 
                                                                           $row.find('.default-menu-name').text().trim();
                                                        if (defaultName) {
                                                            $nameSpan.text(defaultName);
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    });
                                    
                                    // Trigger reorder after updating all attributes
                                    setTimeout(function() {
                                        if (typeof window.reorderSidebarMenu === 'function') {
                                            window.reorderSidebarMenu();
                                        }
                                    }, 100);
                                } else if (typeof window.reorderSidebarMenu === 'function') {
                                    // If sidebar not found, try reorder anyway
                                    window.reorderSidebarMenu();
                                }
                            }, 300);
                            console.log('Menu customizations saved successfully');
                        } else {
                            $status.removeClass('badge-warning').addClass('badge-danger')
                                .html('<i class="fa fa-times"></i> @lang("app.error")');
                        }
                    },
                    error: function(response) {
                        isSaving = false;
                        console.error('Error saving menu customizations:', response);
                        $status.removeClass('badge-warning badge-success').addClass('badge-danger')
                            .html('<i class="fa fa-times"></i> @lang("app.error")');
                        
                        // Show error details in console
                        if (response.responseJSON && response.responseJSON.message) {
                            console.error('Error message:', response.responseJSON.message);
                        }
                    }
                });
            }

            // Debounced save function
            function debouncedSave() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(saveAllMenuCustomizations, 500); // Wait 500ms after last change
            }

            // Initialize Sortable for drag and drop with auto-save
            const menuList = document.getElementById('menu-items-list');
            if (menuList) {
                const sortable = Sortable.create(menuList, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: function(evt) {
                        // Update order numbers in the display immediately
                        $('#menu-items-list .menu-item-row').each(function(index) {
                            const orderSpan = $(this).find('.drag-handle span.ml-1');
                            if (orderSpan.length) {
                                orderSpan.text(index + 1);
                            }
                            // Update data attribute for reference
                            $(this).attr('data-menu-order', index + 1);
                        });
                        
                        // Save immediately after drag and drop (no debounce for drag operations)
                        console.log('Drag and drop completed, saving order...');
                        saveAllMenuCustomizations();
                    }
                });
            }

            // Auto-save on custom name change (with debounce)
            $('body').on('input', '.custom-menu-name', function() {
                debouncedSave();
            });

            // Auto-save on visibility toggle
            $('body').on('change', '.menu-visibility-toggle', function() {
                saveAllMenuCustomizations(); // Immediate save for visibility
            });

            // Reset to default name (with immediate save and sidebar update)
            $('body').on('click', '.reset-menu-name', function() {
                const menuKey = $(this).data('menu-key');
                const defaultName = $(this).data('default-name');
                const $input = $('.custom-menu-name[data-menu-key="' + menuKey + '"]');
                
                // Clear the input value immediately
                $input.val('').attr('placeholder', defaultName);
                
                // Update sidebar immediately (before save)
                const $sidebar = $('.custom-menu-list');
                if ($sidebar.length) {
                    const $sidebarItem = $sidebar.find('li[data-menu-key="' + menuKey + '"]');
                    if ($sidebarItem.length) {
                        // Reset to default name in sidebar
                        const $nameSpan = $sidebarItem.find('a .pl-3, a span.pl-3');
                        if ($nameSpan.length) {
                            $nameSpan.text(defaultName);
                        }
                    }
                }
                
                // Save immediately (no debounce for reset)
                saveAllMenuCustomizations();
            });

            $('#reset-all-menu').on('click', function() {
                Swal.fire({
                    title: "@lang('messages.sweetAlertTitle')",
                    text: '@lang('app.reset')',
                    icon: 'warning',
                    showCancelButton: true,
                    focusConfirm: false,
                    confirmButtonText: "@lang('app.reset')",
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
                    if (!result.isConfirmed) {
                        return;
                    }

                    const $status = $('#save-status');
                    const $btn = $('#reset-all-menu');
                    const $inputs = $('#menu-items-list').find('input, button');

                    $btn.prop('disabled', true).attr('aria-disabled', 'true').text('@lang('app.processing')...');
                    $inputs.prop('disabled', true).attr('aria-disabled', 'true');
                    $('#menu-items-list').css('pointer-events', 'none');

                    const $overlay = $('#global-reset-overlay');
                    const $bar = $('#reset-progress-bar');
                    const $text = $('#reset-progress-text');
                    $overlay.removeClass('d-none');
                    $overlay.attr('aria-busy','true');
                    let start = Date.now();
                    let progressInterval = setInterval(function(){
                        const elapsed = Date.now() - start;
                        const pct = Math.min(95, Math.floor(elapsed / 50));
                        const remainingMs = Math.max(0, 5000 - elapsed);
                        const remainingSec = Math.ceil(remainingMs / 1000);
                        $bar.css('width', pct + '%').attr('aria-valuenow', pct);
                        $text.text(pct + '% • ' + remainingSec + 's');
                    }, 100);

                    $status.removeClass('badge-success badge-danger').addClass('badge-warning')
                        .html('<i class="fa fa-spinner fa-spin"></i> ' + @json(__('app.saving')) + '...')
                        .show();

                    let aborted = false;
                    let resetXhr = $.easyAjax({
                        url: "{{ route('menu-customization.reset-all') }}",
                        type: 'POST',
                        data: { _token: '{{ csrf_token() }}' },
                        blockUI: true,
                        disableButton: true,
                        buttonSelector: '#reset-all-menu',
                        success: function() {
                            $status.removeClass('badge-warning').addClass('badge-success')
                                .html('<i class="fa fa-check"></i> ' + @json(__('app.saved')));

                            clearInterval(progressInterval);

                            var poll = setInterval(function(){
                                $.ajax({
                                    url: "{{ route('menu-customization.progress') }}",
                                    method: 'GET'
                                }).done(function(resp){
                                    var pct = resp.percent || 0;
                                    var remainingSec = resp.eta_secs || 0;
                                    $bar.css('width', pct + '%').attr('aria-valuenow', pct);
                                    $text.text(pct + '% • ' + remainingSec + 's');
                                    if (pct >= 100) {
                                        clearInterval(poll);
                                        $overlay.addClass('d-none').attr('aria-busy','false');
                                        window.location.reload();
                                    }
                                }).fail(function(xhr, textStatus){
                                    if (textStatus === 'abort' || (xhr && xhr.status === 0)) {
                                        return; // ignore aborted requests during navigation
                                    }
                                });
                            }, 500);
                        },
                        error: function() {
                            $status.removeClass('badge-warning badge-success').addClass('badge-danger')
                                .html('<i class="fa fa-times"></i> ' + @json(__('app.error')));
                            clearInterval(progressInterval);
                            $overlay.addClass('d-none').attr('aria-busy','false');
                            if (!aborted) {
                                $btn.prop('disabled', false).attr('aria-disabled','false').text('@lang('app.reset')');
                                $inputs.prop('disabled', false).attr('aria-disabled','false');
                                $('#menu-items-list').css('pointer-events', 'auto');
                                Swal.fire({
                                    icon: 'error',
                                    text: '@lang('messages.errorOccured')',
                                    toast: true,
                                    position: 'top-end',
                                    timer: 3000,
                                    timerProgressBar: true,
                                    showConfirmButton: false,
                                    customClass: { confirmButton: 'btn btn-primary' },
                                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }
                                });
                            }
                        }
                    });

                    const timeoutId = setTimeout(function(){
                        try {
                            aborted = true;
                            if (resetXhr && resetXhr.abort) {
                                resetXhr.abort();
                            }
                        } catch(e) {}
                    }, 10000);

                    $('#reset-cancel').off('click').on('click', function(){
                        if (resetXhr) {
                            aborted = true;
                            resetXhr.abort();
                        }
                        clearTimeout(timeoutId);
                        clearInterval(progressInterval);
                        $overlay.addClass('d-none').attr('aria-busy','false');
                        $btn.prop('disabled', false).attr('aria-disabled','false').text('@lang('app.reset')');
                        $inputs.prop('disabled', false).attr('aria-disabled','false');
                        $('#menu-items-list').css('pointer-events', 'auto');
                        $status.removeClass('badge-warning').addClass('badge-danger').html('<i class="fa fa-times"></i> @lang('app.cancel')');
                    });
                });
            });

            $('#apply-global-default-all').on('click', function() {
                const $status = $('#save-status');
                $status.removeClass('badge-success badge-danger').addClass('badge-warning')
                    .html('<i class="fa fa-spinner fa-spin"></i> Applying...')
                    .show();

                $.easyAjax({
                    url: "{{ route('menu-customization.apply-global-default-all') }}",
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        $status.removeClass('badge-warning').addClass('badge-success')
                            .html('<i class="fa fa-check"></i> Applied');
                        setTimeout(function() { window.location.reload(); }, 800);
                    },
                    error: function() {
                        $status.removeClass('badge-warning badge-success').addClass('badge-danger')
                            .html('<i class="fa fa-times"></i> Error');
                    }
                });
            });
        });
    </script>
@endpush
