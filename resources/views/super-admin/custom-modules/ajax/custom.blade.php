<!-- TAB CONTENT START -->
<div class="tab-pane fade show active" role="tabpanel" aria-labelledby="nav-email-tab">
    <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
        @if (count($allModules) > 0)
            <div class="row">
                @foreach ($allModules as $module)
                    @php
                        $moduleArray = $module->toArray();
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <x-module-card :module="$moduleArray" :type="$type" />
                    </div>
                @endforeach

                @if ($craveva)
                    @php
                        $moduleArray = $craveva->toArray();
                    @endphp
                    <div class="col-xl-3 col-lg-4 col-md-6 mb-3">
                        <x-module-card :module="$moduleArray" :type="$type" />
                    </div>
                @endif
            </div>
        @else
            <div class="row">
                <div class="col-lg-12">
                    <x-cards.no-record icon="user-plus" :message="__('messages.noCustomModuleAdded')" />
                </div>
            </div>
        @endif
    </div>
</div>
<!-- TAB CONTENT END -->