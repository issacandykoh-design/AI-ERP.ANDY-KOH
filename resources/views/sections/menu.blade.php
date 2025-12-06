<ul class="custom-menu-list" style="display: flex !important; flex-direction: column;">
    <!-- NAV ITEM - CRAVEVA AI PRO (Coming Soon) -->
    <x-menu-item icon="gear" :text="'Craveva AI Pro'" :link="'javascript:;'" menu-key="aiPro">
    </x-menu-item>
    <!-- NAV ITEM - DASHBOARD COLLAPSE MENU-->
    @if ((in_array('admin', user_roles())
    || $sidebarUserPermissions['view_overview_dashboard'] == 4
    || $sidebarUserPermissions['view_project_dashboard'] == 4
    || $sidebarUserPermissions['view_client_dashboard'] == 4
    || $sidebarUserPermissions['view_hr_dashboard'] == 4
    || $sidebarUserPermissions['view_ticket_dashboard'] == 4
    || $sidebarUserPermissions['view_finance_dashboard'] == 4
    ) && is_menu_visible('dashboard'))
        <x-menu-item icon="house" :text="__('app.menu.dashboard')" menu-key="dashboard">

            <div class="accordionItemContent">
                <x-sub-menu-item :link="route('dashboard')"
                                 :text="__('app.menu.privateDashboard')" />
                <x-sub-menu-item :link="route('dashboard.advanced')"
                                 :text="__('app.menu.advanceDashboard')" />
            </div>
        </x-menu-item>
    @elseif (is_menu_visible('dashboard'))
        <x-menu-item icon="house" :text="__('app.menu.dashboard')" :link="route('dashboard')" menu-key="dashboard">

        </x-menu-item>
    @endif

    <!-- NAV ITEM - MY CALENDAR -->
    @if ((in_array('tasks', user_modules()) || in_array('events', user_modules()) || in_array('holidays', user_modules()) ||
                in_array('tickets', user_modules()) || in_array('leaves', user_modules())) && is_menu_visible('myCalendar'))
        <x-menu-item icon="calendar-range" :text="__('app.menu.myCalendar')" :link="route('my-calendar.index')" menu-key="myCalendar">

        </x-menu-item>
    @endif

    <!-- NAV ITEM - HR COLLAPASE MENU -->
    @if (!in_array('client', user_roles()) && (in_array('leads', user_modules())) && (($sidebarUserPermissions['view_lead'] != 5 && $sidebarUserPermissions['view_lead'] != 'none') || ($sidebarUserPermissions['view_deals'] != 5 && $sidebarUserPermissions['view_deals'] != 'none')) && is_menu_visible('lead'))
        <x-menu-item icon="person" :text="__('app.menu.lead')" menu-key="lead">

            @if ($sidebarUserPermissions['view_lead'] != 5 && $sidebarUserPermissions['view_lead'] != 'none')
            <div class="accordionItemContent ">
                <x-sub-menu-item :link="route('lead-contact.index')" :text="__('app.leadContact')" />
            </div>
            @endif
            @if ($sidebarUserPermissions['view_deals'] != 5 && $sidebarUserPermissions['view_deals'] != 'none')
                <div class="accordionItemContent ">
                    <x-sub-menu-item :link="route('deals.index')" :text="__('app.deal')" />
                </div>
            @endif
        </x-menu-item>
    @endif


    @if (!in_array('client', user_roles()) && in_array('clients', user_modules()) && $sidebarUserPermissions['view_clients'] != 5 && $sidebarUserPermissions['view_clients'] != 'none' && is_menu_visible('clients'))
        <x-menu-item icon="building" :text="__('app.menu.clients')" :link="route('clients.index')" menu-key="clients">

        </x-menu-item>
    @endif

<!-- NAV ITEM - HR COLLAPASE MENU -->
    @if (!in_array('client', user_roles()) && (in_array('employees', user_modules()) || in_array('leaves', user_modules()) || in_array('attendance', user_modules()) || in_array('holidays', user_modules())) && ($sidebarUserPermissions['view_employees'] != 5 || $sidebarUserPermissions['view_leave'] != 5 || $sidebarUserPermissions['view_attendance'] != 5 || $sidebarUserPermissions['view_holiday'] != 5) && ($sidebarUserPermissions['view_employees'] != 'none' || $sidebarUserPermissions['view_leave'] != 'none' || $sidebarUserPermissions['view_attendance'] != 'none' || $sidebarUserPermissions['view_holiday'] != 'none' || $sidebarUserPermissions['view_shift_roster'] != 'none') && is_menu_visible('hr'))
        <x-menu-item icon="people" :text="__('app.menu.hr')" menu-key="hr">

            <div class="accordionItemContent">
                @if (in_array('employees', user_modules()) && $sidebarUserPermissions['view_employees'] != 5 && $sidebarUserPermissions['view_employees'] != 'none')
                    <x-sub-menu-item :link="route('employees.index')" :text="__('app.menu.employees')" />
                @endif
                @if (in_array('leaves', user_modules()) && $sidebarUserPermissions['view_leave'] != 5 && $sidebarUserPermissions['view_leave'] != 'none')
                    <x-sub-menu-item :link="route('leaves.index')" :text="__('app.menu.leaves')" />
                @endif
                @if (in_array('attendance', user_modules()) && isset($sidebarUserPermissions['view_shift_roster']) && $sidebarUserPermissions['view_shift_roster'] != 5 && $sidebarUserPermissions['view_shift_roster'] != 'none')
                    <x-sub-menu-item :link="route('shifts.index')" :text="__('app.menu.shiftRoster')" />
                @endif
                @if (in_array('attendance', user_modules()) && $sidebarUserPermissions['view_attendance'] != 5 && $sidebarUserPermissions['view_attendance'] != 'none')
                    <x-sub-menu-item :link="route('attendances.index')" :text="__('app.menu.attendance')" />
                @endif
                @if (in_array('holidays', user_modules()) && $sidebarUserPermissions['view_holiday'] != 5 && $sidebarUserPermissions['view_holiday'] != 'none')
                    <x-sub-menu-item :link="route('holidays.index')" :text="__('app.menu.holiday')" />
                @endif
                @if (isset($sidebarUserPermissions['view_designation']) && $sidebarUserPermissions['view_designation'] == 4 )
                    <x-sub-menu-item :link="route('designations.index')" :text="__('app.menu.designation')" />
                @endif
                @if (isset($sidebarUserPermissions['view_department']) && $sidebarUserPermissions['view_department'] == 4)
                    <x-sub-menu-item :link="route('departments.index')" :text="__('app.menu.department')" />
                @endif
                @if (isset($sidebarUserPermissions['view_appreciation']) && $sidebarUserPermissions['view_appreciation'] != 5)
                    <x-sub-menu-item :link="route('appreciations.index')" :text="__('app.menu.appreciation')" />
                @endif
                @if (isset($sidebarUserPermissions['view_appreciation'])  && $sidebarUserPermissions['view_appreciation'] == 5 && isset($sidebarUserPermissions['manage_award']) && $sidebarUserPermissions['manage_award'] == 4)
                    <x-sub-menu-item :link="route('awards.index')" :text="__('app.menu.appreciation')" />
                @endif
                <!-- NAV ITEM - CUSTOM MODULES  -->
                @foreach ($cravevaPlugins as $item)
                    @includeIf(strtolower($item) . '::sections.hr.sidebar')
                @endforeach
            </div>
        </x-menu-item>
    @endif

<!-- NAV ITEM - WORK COLLAPSE MENU -->
    @if ((in_array('contracts', user_modules()) || in_array('projects', user_modules()) || in_array('tasks', user_modules()) || in_array('timelogs', user_modules())) && ($sidebarUserPermissions['view_contract'] != 5 || $sidebarUserPermissions['view_projects'] != 5 || $sidebarUserPermissions['view_tasks'] != 5 || $sidebarUserPermissions['view_timelogs'] != 5) && ($sidebarUserPermissions['view_contract'] != 'none' || $sidebarUserPermissions['view_projects'] != 'none' || $sidebarUserPermissions['view_tasks'] != 'none' || $sidebarUserPermissions['view_timelogs'] != 'none') && is_menu_visible('work'))
<x-menu-item icon="briefcase" :text="__('app.menu.work')" menu-key="work">

            <div class="accordionItemContent">
                @if (in_array('contracts', user_modules()) && $sidebarUserPermissions['view_contract'] != 5 && $sidebarUserPermissions['view_contract'] != 'none')
                    <x-sub-menu-item :link="route('contracts.index')" :text="__('app.menu.contracts')" />
                @endif
                @if (in_array('projects', user_modules()) && $sidebarUserPermissions['view_projects'] != 5 && $sidebarUserPermissions['view_projects'] != 'none')
                    <x-sub-menu-item :link="route('projects.index')" :text="__('app.menu.projects')" />
                @endif
                {{-- @if (!in_array('client', user_roles())) --}}
                @if (in_array('tasks', user_modules()) && $sidebarUserPermissions['view_tasks'] != 5 && $sidebarUserPermissions['view_tasks'] != 'none')
                    <x-sub-menu-item :link="route('tasks.index')" :text="__('app.menu.tasks')" />
                @endif
                @if (in_array('timelogs', user_modules()) && $sidebarUserPermissions['view_timelogs'] != 5 && $sidebarUserPermissions['view_timelogs'] != 'none')
                    <x-sub-menu-item :link="route('timelogs.index')" :text="__('app.menu.timeLogs')" />
                @endif
                {{-- @endif --}}
                <!-- NAV ITEM - CUSTOM MODULES  -->
                @foreach ($cravevaPlugins as $item)
                    @includeIf(strtolower($item) . '::sections.work.sidebar')
                @endforeach
            </div>
        </x-menu-item>
    @endif

<!-- NAV ITEM - FINANCE COLLAPASE MENU -->
    @if ((in_array('estimates', user_modules()) || in_array('invoices', user_modules()) || in_array('payments', user_modules()) || in_array('expenses', user_modules()) || in_array('bankaccount', user_modules())) && ($sidebarUserPermissions['view_estimates'] != 5 || $sidebarUserPermissions['view_invoices'] != 5 || $sidebarUserPermissions['view_payments'] != 5 || $sidebarUserPermissions['view_expenses'] != 5 || $sidebarUserPermissions['view_lead_proposals'] != 5 || $sidebarUserPermissions['view_bankaccount'] != 5) && ($sidebarUserPermissions['view_estimates'] != 'none' || $sidebarUserPermissions['view_invoices'] != 'none' || $sidebarUserPermissions['view_payments'] != 'none' || $sidebarUserPermissions['view_expenses'] != 'none' || $sidebarUserPermissions['view_lead_proposals'] != 'none' || $sidebarUserPermissions['view_bankaccount'] != 'none') && is_menu_visible('finance'))
        <x-menu-item icon="cash-coin" :active="($currentRouteName === 'payments.index')"
                     :text="__('app.menu.finance')" menu-key="finance">

            <div class="accordionItemContent">
                @if (in_array('leads', user_modules()) && $sidebarUserPermissions['view_lead_proposals'] != 5 && $sidebarUserPermissions['view_lead_proposals'] != 'none')
                    <x-sub-menu-item :link="route('proposals.index')" :text="__('app.menu.proposal')" />
                @endif
                @if (in_array('estimates', user_modules()) && $sidebarUserPermissions['view_estimates'] != 5 && $sidebarUserPermissions['view_estimates'] != 'none')
                    <x-sub-menu-item :link="route('estimates.index')" :text="__('app.menu.estimates')" />
                @endif
                @if (in_array('invoices', user_modules()) && $sidebarUserPermissions['view_invoices'] != 5 && $sidebarUserPermissions['view_invoices'] != 'none')
                    <x-sub-menu-item :link="route('invoices.index')" :text="__('app.menu.invoices')" />
                @endif
                @if (in_array('payments', user_modules()) && $sidebarUserPermissions['view_payments'] != 5 && $sidebarUserPermissions['view_payments'] != 'none')
                    <x-sub-menu-item :link="route('payments.index')" :text="__('app.menu.payments')" />
                @endif
                @if (in_array('invoices', user_modules()) && $sidebarUserPermissions['view_invoices'] != 5 && $sidebarUserPermissions['view_invoices'] != 'none')
                    <x-sub-menu-item :link="route('creditnotes.index')"
                                     :text="__('app.menu.credit-note')" />
                @endif

                @if (in_array('expenses', user_modules()) && $sidebarUserPermissions['view_expenses'] != 5 && $sidebarUserPermissions['view_expenses'] != 'none')
                    <x-sub-menu-item :link="route('expenses.index')" :text="__('app.menu.expenses')" />
                @endif

                @if (in_array('bankaccount', user_modules()) && $sidebarUserPermissions['view_bankaccount'] != 5 && $sidebarUserPermissions['view_bankaccount'] != 'none')
                    <x-sub-menu-item :link="route('bankaccounts.index')" :text="__('app.menu.bankaccount')" />
                @endif
                <!-- NAV ITEM - CUSTOM MODULES  -->
                @foreach ($cravevaPlugins as $item)
                    @includeIf(strtolower($item) . '::sections.finance.sidebar')
                @endforeach
            </div>
        </x-menu-item>
    @endif

<!-- NAV ITEM - PRODUCTS -->
@if (!in_array('purchase', user_modules()) && in_array('products', user_modules()) && $sidebarUserPermissions['view_product'] != 5 && $sidebarUserPermissions['view_product'] != 'none' && is_menu_visible('products'))
        <x-menu-item icon="basket" :text="__('app.menu.products')" :link="route('products.index')" menu-key="products">

        </x-menu-item>
@endif

<!-- NAV ITEM - PRODUCTS -->
    @if (in_array('orders', user_modules()) && $sidebarUserPermissions['view_order'] != 5 && $sidebarUserPermissions['view_order'] != 'none' && is_menu_visible('orders'))
        <x-menu-item icon="cart3" :text="__('app.menu.orders')" :link="route('orders.index')" menu-key="orders">

        </x-menu-item>
    @endif

<!-- NAV ITEM - TICKETS -->
    @if (in_array('tickets', user_modules()) && $sidebarUserPermissions['view_tickets'] != 5 && $sidebarUserPermissions['view_tickets'] != 'none' && is_menu_visible('tickets'))
        <x-menu-item icon="headset" :text="__('app.menu.tickets')" :link="route('tickets.index')" menu-key="tickets">

        </x-menu-item>
    @endif


<!-- NAV ITEM - EVENTS -->
    @if (in_array('events', user_modules()) && $sidebarUserPermissions['view_events'] != 5 && $sidebarUserPermissions['view_events'] != 'none' && is_menu_visible('events'))
        <x-menu-item icon="calendar-event" :text="__('app.menu.events')" :link="route('events.index')" menu-key="events">

        </x-menu-item>
    @endif

<!-- NAV ITEM - MESSAGES -->
    @if (in_array('messages', user_modules()) && is_menu_visible('messages'))
        @if ((message_setting()->allow_client_admin == 'yes' || message_setting()->allow_client_employee == 'yes') && in_array('client', user_roles()))
            <x-menu-item class="message-menu" icon="chat-left-text" :text="__('app.menu.messages')"
                         :count="$unreadMessagesCount" :link="route('messages.index')" menu-key="messages">

            </x-menu-item>
        @elseif (in_array('employee', user_roles()) || in_array('admin', user_roles()))
            <x-menu-item class="message-menu" icon="chat-left-text" :text="__('app.menu.messages')"
                         :link="route('messages.index')" :count="$unreadMessagesCount" menu-key="messages">

            </x-menu-item>
        @endif
    @endif

<!-- NAV ITEM - GDPR -->
    @if ((in_array('admin', user_roles()) || in_array('client', user_roles())) && $gdpr->enable_gdpr == 1 && is_menu_visible('gdpr'))
        <x-menu-item icon="lock" :text="__('app.menu.gdpr')" :link="route('gdpr.index')" menu-key="gdpr">

        </x-menu-item>
    @endif

<!-- NAV ITEM - NOTICES -->
    @if (in_array('notices', user_modules()) && $sidebarUserPermissions['view_notice'] != 5 && $sidebarUserPermissions['view_notice'] != 'none' && is_menu_visible('noticeBoard'))
        <x-menu-item icon="clipboard" :text="__('app.menu.noticeBoard')" :link="route('notices.index')" menu-key="noticeBoard">

        </x-menu-item>
    @endif

<!-- Knowledge base -->
    @if (in_array('knowledgebase', user_modules()) && isset($sidebarUserPermissions['view_knowledgebase']) && $sidebarUserPermissions['view_knowledgebase'] != 5 && is_menu_visible('knowledgebase'))
        <x-menu-item icon="note" :text="__('app.menu.knowledgebase')" :link="route('knowledgebase.index')" menu-key="knowledgebase">

        </x-menu-item>
    @endif
<!-- Knowledge base -->


    <!-- NAV ITEM - NOTES -->
    @if (in_array('client', user_roles()) && $sidebarUserPermissions['view_client_note'] != 5 && is_menu_visible('notes'))
        <x-menu-item icon="journal-text" :text="__('app.menu.notes')" :link="route('client-notes.index')" menu-key="notes">
        </x-menu-item>
    @endif
@if (checkCompanyPackageIsValid(user()->company_id))
<!-- NAV ITEM - CUSTOM MODULES  -->
    @foreach ($cravevaPlugins as $item)
        @includeIf(strtolower($item) . '::sections.sidebar')
    @endforeach
@endif
<!-- NAV ITEM - NOTICES -->
    @if (in_array('reports', user_modules()) && ($sidebarUserPermissions['view_task_report'] == 4 || $sidebarUserPermissions['view_time_log_report'] == 4 || (isset($sidebarUserPermissions['view_expense_report']) && $sidebarUserPermissions['view_expense_report'] == 4) || $sidebarUserPermissions['view_finance_report'] != 5 || $sidebarUserPermissions['view_income_expense_report'] == 4 || $sidebarUserPermissions['view_leave_report'] != 5 || $sidebarUserPermissions['view_attendance_report'] == 4 || $sidebarUserPermissions['view_lead_report'] == 4 || $sidebarUserPermissions['view_sales_report'] == 4) && ($sidebarUserPermissions['view_task_report'] != 'none' || $sidebarUserPermissions['view_time_log_report'] != 'none' || $sidebarUserPermissions['view_finance_report'] != 'none' || $sidebarUserPermissions['view_income_expense_report'] != 'none' || $sidebarUserPermissions['view_leave_report'] != 'none' || $sidebarUserPermissions['view_attendance_report'] != 'none' || $sidebarUserPermissions['view_lead_report'] != 'none' || $sidebarUserPermissions['view_sales_report'] != 'none' || (isset($sidebarUserPermissions['view_expense_report']) && $sidebarUserPermissions['view_expense_report'] != 'none')) && is_menu_visible('reports'))
        <x-menu-item icon="graph-up" :text="__('app.menu.reports')" menu-key="reports">

            <div class="accordionItemContent">
                @if ($sidebarUserPermissions['view_task_report'] == 4 && $sidebarUserPermissions['view_task_report'] != 'none' && in_array('tasks', user_modules()))
                    <x-sub-menu-item :link="route('task-report.index')" :text="__('app.menu.taskReport')" />
                @endif

                @if ($sidebarUserPermissions['view_time_log_report'] == 4 && $sidebarUserPermissions['view_time_log_report'] != 'none' && in_array('timelogs', user_modules()))
                    <x-sub-menu-item :link="route('time-log-report.index')"
                                     :text="__('app.menu.timeLogReport')" />
                @endif

               @if ($sidebarUserPermissions['view_time_log_report'] == 4 && $sidebarUserPermissions['view_time_log_report'] != 'none' && in_array('timelogs', user_modules()))
                    <x-sub-menu-item :link="route('time-log-weekly-report.index')"
                                     :text="__('app.menu.weeklyTimehsheet')" />
                @endif

                @if ($sidebarUserPermissions['view_finance_report'] != 5 && $sidebarUserPermissions['view_finance_report'] != 'none' && in_array('payments', user_modules()))
                    <x-sub-menu-item :link="route('finance-report.index')"
                                     :text="__('app.menu.financeReport')" />
                @endif

                @if ($sidebarUserPermissions['view_income_expense_report'] == 4 && $sidebarUserPermissions['view_income_expense_report'] != 'none' && in_array('expenses', user_modules()))
                    <x-sub-menu-item :link="route('income-expense-report.index')"
                                     :text="__('app.menu.incomeVsExpenseReport')" />
                @endif

                @if ($sidebarUserPermissions['view_leave_report'] != 5 && $sidebarUserPermissions['view_leave_report'] != 'none' && in_array('leaves', user_modules()))
                    <x-sub-menu-item :link="route('leave-report.leave_quota')"
                                     :text="__('app.menu.leaveReport')" />
                @endif

                @if ($sidebarUserPermissions['view_attendance_report'] == 4 && $sidebarUserPermissions['view_attendance_report'] != 'none' && in_array('attendance', user_modules()))
                    <x-sub-menu-item :link="route('attendance-report.index')"
                                     :text="__('app.menu.attendanceReport')" />
                @endif
                @if (isset($sidebarUserPermissions['view_expense_report']) && $sidebarUserPermissions['view_expense_report'] == 4 && $sidebarUserPermissions['view_expense_report'] != 'none' && in_array('expenses', user_modules()))
                    <x-sub-menu-item :link="route('expense-report.index')"
                                     :text="__('app.menu.expenseReport')" />
                @endif
                @if (isset($sidebarUserPermissions['view_lead_report']) && $sidebarUserPermissions['view_lead_report'] == 4 && $sidebarUserPermissions['view_lead_report'] != 'none' && in_array('leads', user_modules()))
                    <x-sub-menu-item :link="route('lead-report.index')"
                                     :text="__('app.menu.dealReport')" />
                @endif
                @if (isset($sidebarUserPermissions['view_sales_report']) && $sidebarUserPermissions['view_sales_report'] == 4 && $sidebarUserPermissions['view_sales_report'] != 'none' && in_array('invoices', user_modules()))
                    <x-sub-menu-item :link="route('sales-report.index')"
                                     :text="__('app.menu.salesReport')" />
                @endif
            </div>
        </x-menu-item>
@endif

<!-- NAV ITEM - CUSTOM LINK -->

    @php
    $role = user()->role->last();
    @endphp

    @foreach ($customLink as $item)
        @if((in_array($role->role_id, json_decode($item->can_be_viewed_by)) || in_array('admin', user_roles())) && $item->status == 'active')
            <li>
                <a class="nav-item text-lightest f-15 sidebar-text-color" href={{$item->url}} target="_blank"
                title={{$item->link_title}}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        class="bi bi-link" viewBox="0 0 16 16">
                        <path d="M6.354 5.5H4a3 3 0 0 0 0 6h3a3 3 0 0 0 2.83-4H9c-.086 0-.17.01-.25.031A2 2 0 0 1 7 10.5H4a2 2 0 1 1 0-4h1.535c.218-.376.495-.714.82-1z"/>
                        <path d="M9 5.5a3 3 0 0 0-2.83 4h1.098A2 2 0 0 1 9 6.5h3a2 2 0 1 1 0 4h-1.535a4.02 4.02 0 0 1-.82 1H12a3 3 0 1 0 0-6H9z"/>
                    </svg>
                    <span class="pl-3">{{$item->link_title}}</span>
                </a>
            </li>
        @endif
    @endforeach

<!-- NAV ITEM - REPORTS COLLAPASE MENU -->
    <!-- NAV ITEM - SETTINGS -->
    @if (is_menu_visible('settings'))
    <x-menu-item icon="gear" :text="__('app.menu.settings')"
                 :link="($sidebarUserPermissions['manage_company_setting'] == 4 ? route('company-settings.index') : route('profile-settings.index'))" menu-key="settings">
    </x-menu-item>
    @endif

    @if(iscravevaSaas())
        @if (in_array('admin', user_roles()) )
        <div class="d-block d-lg-none d-xl-none">
            <x-menu-item icon="question" :text="__('app.menu.help')" :link="route('superadmin.faqs.index')" menu-key="help">
            </x-menu-item>
        </div>
        @endif
    @endif


</ul>

@push('scripts')
<script>
    $(document).ready(function() {
        // AI Pro coming soon popup
        $('body').on('click', 'li[data-menu-key="aiPro"] > a', function(e) {
            e.preventDefault();
            let modal = document.getElementById('ai-pro-coming-soon');
            if (!modal) {
                const html = '
                <div class="modal fade" id="ai-pro-coming-soon" tabindex="-1" role="dialog" aria-hidden="true">
                  <div class="modal-dialog modal-sm modal-dialog-centered" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Craveva AI Pro</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                      </div>
                      <div class="modal-body">
                        <p class="mb-0">Coming Soon</p>
                      </div>
                    </div>
                  </div>
                </div>';
                $('body').append(html);
                modal = document.getElementById('ai-pro-coming-soon');
            }
            if ($.fn && $.fn.modal) {
                $('#ai-pro-coming-soon').modal('show');
            } else {
                const toastId = 'ai-pro-toast';
                if (!document.getElementById(toastId)) {
                    const toast = document.createElement('div');
                    toast.id = toastId;
                    toast.style.position = 'fixed';
                    toast.style.top = '20px';
                    toast.style.right = '20px';
                    toast.style.zIndex = '1050';
                    toast.style.background = '#222';
                    toast.style.color = '#fff';
                    toast.style.padding = '10px 14px';
                    toast.style.borderRadius = '6px';
                    toast.style.boxShadow = '0 2px 6px rgba(0,0,0,0.2)';
                    toast.textContent = 'Craveva AI Pro — Coming Soon';
                    document.body.appendChild(toast);
                    setTimeout(function(){
                        if (toast && toast.parentNode) toast.parentNode.removeChild(toast);
                    }, 2500);
                }
            }
        });
        let isReordering = false;
        let hasReordered = false;
        
        // Reorder menu items based on custom order
        var reorderMenuItems = function(force) {
            // Prevent multiple simultaneous reorders
            if (isReordering && !force) {
                return;
            }
            
            const menuList = $('.custom-menu-list');
            if (!menuList.length) {
                return;
            }

            const menuItems = menuList.find('li').toArray();
            if (menuItems.length === 0) {
                return;
            }

            isReordering = true;
            const itemsWithOrder = [];
            let needsReorder = false;

            // Apply code-defined global defaults for items without a valid order
            const globalOrderKeys = [
                'aipro','dashboard','noticeboard','orders','purchase','lead','clients','finance','reports',
                'work','mycalendar','events','messages','letter','hr','payroll','performance','knowledgebase','recruit','asset',
                'settings','help','tickets','servermanager','gdpr','qrcode','biolinks','biometric','webhooks','zoom'
            ];
            const getDefaultOrder = function(key) {
                const idx = globalOrderKeys.indexOf(String(key || '').toLowerCase());
                return idx === -1 ? 999 : (idx + 1);
            };

            // Get order for each menu item
            menuItems.forEach(function(item, index) {
                const $item = $(item);
                let order = 999;
                
                // Get menu-key from the li element
                const menuKey = $item.attr('data-menu-key') || '';
                
                // Try to get from data-menu-order attribute
                let dataOrder = $item.attr('data-menu-order');
                
                if (dataOrder && dataOrder !== 'undefined' && dataOrder !== 'null' && dataOrder !== '' && !isNaN(dataOrder)) {
                    order = parseInt(dataOrder, 10);
                    if (order < 1 || order > 998) {
                        order = 999;
                    }
                }

                // If no valid order from server, apply global default
                if (order === 999) {
                    const def = getDefaultOrder(menuKey);
                    if (def > 0 && def < 999) {
                        order = def;
                        $item.attr('data-menu-order', def);
                    }
                }
                
                itemsWithOrder.push({ 
                    element: item, 
                    order: order, 
                    menuKey: menuKey,
                    originalIndex: index
                });
            });
            
            // Sort by order
            itemsWithOrder.sort(function(a, b) {
                if (a.order !== b.order) {
                    return a.order - b.order;
                }
                return a.originalIndex - b.originalIndex;
            });
            
            // Check if reordering is actually needed
            for (let i = 0; i < itemsWithOrder.length; i++) {
                const currentIndex = menuItems.indexOf(itemsWithOrder[i].element);
                if (currentIndex !== i) {
                    needsReorder = true;
                    break;
                }
            }
            
            // Only reorder if needed and not already reordered
            if (needsReorder && (!hasReordered || force)) {
                // Temporarily disable MutationObserver to prevent loops
                if (window.menuMutationObserver) {
                    window.menuMutationObserver.disconnect();
                }
                
                // Reorder using document fragment for better performance
                const fragment = document.createDocumentFragment();
                itemsWithOrder.forEach(function(item) {
                    fragment.appendChild(item.element);
                });
                
                menuList[0].innerHTML = '';
                menuList[0].appendChild(fragment);
                
                hasReordered = true;
                
                // Re-enable MutationObserver after a short delay
                if (window.menuMutationObserver) {
                    setTimeout(function() {
                        const menuListEl = document.querySelector('.custom-menu-list');
                        if (menuListEl) {
                            window.menuMutationObserver.observe(menuListEl, {
                                childList: true,
                                subtree: false
                            });
                        }
                    }, 500);
                }
            }
            
            isReordering = false;
        };

        // Run once after DOM is ready
        setTimeout(function() {
            reorderMenuItems();
        }, 500);
        
        // Watch for DOM changes, but only if menu items are actually added/removed
        // and only trigger once, not continuously
        if (typeof MutationObserver !== 'undefined') {
            let reorderTimeout;
            let observerActive = true;
            
            const observer = new MutationObserver(function(mutations) {
                if (!observerActive) {
                    return;
                }
                
                let shouldReorder = false;
                mutations.forEach(function(mutation) {
                    // Only reorder if menu items (li) are added/removed from outside
                    // Ignore changes we made ourselves
                    for (let i = 0; i < mutation.addedNodes.length; i++) {
                        const node = mutation.addedNodes[i];
                        if (node.nodeName === 'LI' && !node.hasAttribute('data-reordered')) {
                            shouldReorder = true;
                            break;
                        }
                    }
                    for (let i = 0; i < mutation.removedNodes.length; i++) {
                        const node = mutation.removedNodes[i];
                        if (node.nodeName === 'LI' && !node.hasAttribute('data-reordered')) {
                            shouldReorder = true;
                            break;
                        }
                    }
                });
                
                if (shouldReorder && !hasReordered) {
                    clearTimeout(reorderTimeout);
                    reorderTimeout = setTimeout(function() {
                        reorderMenuItems(true);
                    }, 1000);
                }
            });
            
            window.menuMutationObserver = observer;
            
            const menuList = document.querySelector('.custom-menu-list');
            if (menuList) {
                observer.observe(menuList, {
                    childList: true,
                    subtree: false
                });
            }
        }
        
        // Make reorderMenuItems available globally so it can be called from menu customization page
        window.reorderSidebarMenu = function() {
            hasReordered = false;
            reorderMenuItems(true);
        };
    });
</script>
@endpush
