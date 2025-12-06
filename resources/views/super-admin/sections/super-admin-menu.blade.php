<ul>
    @if(user()->is_superadmin)
        <x-menu-item icon="house" :text="__('app.menu.dashboard')" :link="route('superadmin.super_admin_dashboard')" menu-key="dashboard">
        </x-menu-item>

        @if($sidebarSuperadminPermissions['view_packages'] != 5 && $sidebarSuperadminPermissions['view_packages'] != 'none')
            <x-menu-item icon="box2" :text="__('superadmin.menu.packages')" :link="route('superadmin.packages.index')" menu-key="packages">
            </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['view_companies'] != 5 && $sidebarSuperadminPermissions['view_companies'] != 'none')

            <x-menu-item icon="building" :text="__('superadmin.menu.companies')" :link="route('superadmin.companies.index')" menu-key="companies">
            </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['manage_billing'] != 5 && $sidebarSuperadminPermissions['manage_billing'] != 'none')

            <x-menu-item icon="receipt" :text="__('superadmin.menu.billing')" :link="route('superadmin.superadmin-invoices.index')" menu-key="billing">
            </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['view_admin_faq'] != 5 && $sidebarSuperadminPermissions['view_admin_faq'] != 'none')

            <x-menu-item icon="files" :text="__('superadmin.menu.adminFaq')" :link="route('superadmin.faqs.index')" menu-key="adminFaq">
            </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['view_superadmin'] != 5 && $sidebarSuperadminPermissions['view_superadmin'] != 'none')

        <x-menu-item icon="person" :text="__('superadmin.menu.superAdmin')" :link="route('superadmin.superadmin.index')">
        </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['view_request'] != 5 && $sidebarSuperadminPermissions['view_request'] != 'none')

        <x-menu-item icon="incognito" :text="__('superadmin.menu.offlineRequest')" :link="route('superadmin.offline-plan.index')" :count="$totalPendingOfflineRequests ?? 0" menu-key="offlineRequest">
        </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['view_superadmin_ticket'] != 5 && $sidebarSuperadminPermissions['view_superadmin_ticket'] != 'none')

        <x-menu-item icon="ticket-detailed" :text="__('superadmin.menu.supportTicket')" :link="route('superadmin.support-tickets.index')" :count="$totalOpenTickets ?? 0" menu-key="supportTicket">
        </x-menu-item>
        @endif

        @if($sidebarSuperadminPermissions['manage_superadmin_front_settings'] != 5 && $sidebarSuperadminPermissions['manage_superadmin_front_settings'] != 'none')

        <x-menu-item icon="front" :text="__('superadmin.menu.frontSettings')" :link="route('superadmin.front-settings.front_theme_settings')" menu-key="frontSettings">
        </x-menu-item>
       @endif

        @foreach (craveva_plugins() as $item)
            @includeIf(strtolower($item).'::sections.superadmin.sidebar')
        @endforeach

        <x-menu-item icon="binoculars" :text="__('superadmin.menu.telescope')" menu-key="telescope">
            <div class="accordionItemContent pb-2">
                <x-sub-menu-item :link="url('account/telescope-dashboard')" :text="'Dashboard'"/>
                <x-sub-menu-item :link="url('telescope/requests')" :text="'Requests'"/>
                <x-sub-menu-item :link="url('telescope/commands')" :text="'Commands'"/>
                <x-sub-menu-item :link="url('telescope/schedule')" :text="'Schedule'"/>
                <x-sub-menu-item :link="url('telescope/jobs')" :text="'Jobs'"/>
                <x-sub-menu-item :link="url('telescope/batches')" :text="'Batches'"/>
                <x-sub-menu-item :link="url('telescope/cache')" :text="'Cache'"/>
                <x-sub-menu-item :link="url('telescope/dumps')" :text="'Dumps'"/>
                <x-sub-menu-item :link="url('telescope/events')" :text="'Events'"/>
                <x-sub-menu-item :link="url('telescope/exceptions')" :text="'Exceptions'"/>
                <x-sub-menu-item :link="url('telescope/gates')" :text="'Gates'"/>
                <x-sub-menu-item :link="url('telescope/http-client')" :text="'HTTP Client'"/>
                <x-sub-menu-item :link="url('telescope/logs')" :text="'Logs'"/>
                <x-sub-menu-item :link="url('telescope/mail')" :text="'Mail'"/>
                <x-sub-menu-item :link="url('telescope/models')" :text="'Models'"/>
                <x-sub-menu-item :link="url('telescope/notifications')" :text="'Notifications'"/>
                <x-sub-menu-item :link="url('telescope/queries')" :text="'Queries'"/>
                <x-sub-menu-item :link="url('telescope/redis')" :text="'Redis'"/>
                <x-sub-menu-item :link="url('telescope/views')" :text="'Views'"/>
            </div>
        </x-menu-item>

        <x-menu-item icon="gear" :text="__('superadmin.menu.setting')"
         :link="(user()->permission('manage_superadmin_app_settings') == 'all' ? route('app-settings.index') : route('superadmin.settings.super-admin-profile.index'))" menu-key="settings">
        </x-menu-item>
    @endif
</ul>

@push('scripts')
<script>
    $(function() {
        // Superadmin sidebar reorder using global defaults (independent of company settings)
        var orderKeys = [
            'aipro','dashboard','noticeboard','orders','purchase','lead','clients','finance','reports',
            'work','mycalendar','events','messages','letter','hr','payroll','performance','knowledgebase','recruit','asset',
            'settings','help','tickets','servermanager','gdpr','qrcode','biolinks','biometric','webhooks','zoom',
            // Superadmin-specific
            'packages','companies','billing','adminfaq','offlinerequest','supportticket','frontsettings','telescope'
        ];

        function getOrderFor(key) {
            var idx = orderKeys.indexOf(String(key || '').toLowerCase());
            return idx === -1 ? 999 : (idx + 1);
        }

        var $menu = $('ul').first();
        var items = $menu.children('li').get().map(function(el) {
            var $el = $(el);
            var key = $el.data('menu-key');
            var order = getOrderFor(key);
            $el.attr('data-menu-order', order);
            return { el: el, order: order };
        });

        items.sort(function(a, b) { return a.order - b.order; });
        var frag = document.createDocumentFragment();
        items.forEach(function(it) { frag.appendChild(it.el); });
        $menu[0].innerHTML = '';
        $menu[0].appendChild(frag);
    });
</script>
@endpush
