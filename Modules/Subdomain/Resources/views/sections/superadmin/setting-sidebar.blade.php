<x-setting-menu-item
    :active="$activeMenu" menu="subdomain_setting"
    :href="route('super-admin.get.banned-subdomains')"
    :text="__('app.menu.subdomainSetting')">
</x-setting-menu-item>
