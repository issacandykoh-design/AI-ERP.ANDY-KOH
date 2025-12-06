@if (global_setting()->system_update == 1 &&  (in_array('admin', user_roles())|| user()->is_superadmin))
    @php
        $updateVersionInfo = \Craveva\Craveva\Functions\CravevaUpdate::updateVersionInfo();
    @endphp
    @if (isset($updateVersionInfo['lastVersion']))
        <div class="col-md-12">
            <x-alert type="info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fa fa-gift"></i> @lang('modules.update.newUpdate') <span
                            class="badge badge-success">{{ $updateVersionInfo['lastVersion'] }}</span>
                    </div>
                    <div>
                        <span class="text-muted">
                            <i class="fa fa-info-circle"></i> 更新功能已禁用
                        </span>
                    </div>

                </div>
            </x-alert>
        </div>
    @endif

@endif
