<style>
 .aside-back {
    background: #001B63 !important;
    color: #fff;
    font-size: 12px;
}

.nav-sub {
    background-color:#0A2C80 !important;
}
.navbar .active > .nav-link, .navbar .nav-link.active, .navbar .nav-link.show, .navbar .show > .nav-link {
    color: #fff;
    background-color:#1F439A  ;
}
.navbar .nav-link:hover {
    color: #fff;
}
.nav-subtitle {
    display: block;
    color: #4F5B67;
        background-color:#1F439A  ;

    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .03125rem;
}
h1{
        font-size:12px;

}
.navbar-vertical .nav-link:hover .nav-indicator-icon {
    color: #1F439A; /* موف عند التمرير إذا لم يكن نشطًا */
}


.navbar-vertical .active .nav-link:hover .nav-indicator-icon {
    color: #fff !important; /* أبيض عند التمرير إذا كان العنصر نشطًا */
}

.nav-icon{
        color: #fff;

}
.nav-icon:hover,
.nav-icon:focus,
.nav-icon:active {
    color: #fff;
}


.nav-indicator-icon {
    color: #ffffff;
    font-size: 6px;
    -ms-flex: 0 0 1rem;
    flex: 0 0 1rem;
}
.direction-toggle {
    background: #161853;
    color: #ffffff;
    padding: 8px 0;
    -webkit-padding-end: 18px;
    padding-inline-end: 18px;
    -webkit-padding-start: 10px;
    padding-inline-start: 10px;
    cursor: pointer;
    position: fixed;
    top: 30%;
    border-radius: 5px;
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all ease 0.3s;
    white-space: nowrap;
    inset-inline-start: 100%;
    transform: translateX(calc(-100% + 3px));
}
::-webkit-scrollbar-thumb:hover {
    background: #ffffff;
}
.i{
    color:white;
        font-weight: normal;

}
@font-face {
    font-family: 'Bahij';
    src: url("{{ asset('public/assets/admin/css/fonts/Bahij_TheSansArabic-Plain.ttf') }}") format('truetype');
    font-weight: normal;
    font-style: normal;
}

body {
    font-family: 'Bahij', sans-serif;
    font-weight: 150;
    color: black;
    background:rgba(173, 216, 230, 0.2);
}
.navbar-vertical:not([class*=container]) .navbar-nav .nav-link {
    padding: .8rem 1.75rem;
}
.navbar-vertical .navbar-nav.nav-tabs .nav-item:not(:last-child) {
    margin: 0;
    background-color: #1F439A;
}
.nav-link.active .nav-icon {
    color: #fff !important;
}
.paginate_button{
    display: none;
}
.dataTables_length{
     display: none;
   
}
input[type="search"][aria-controls="DataTables_Table_0"] {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_0"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_1"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_2"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_3"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_4"]) {
    display: none;
}
label:has(input[type="search"][aria-controls="DataTables_Table_5"]) {
    display: none;
}
#DataTables_Table_0_info{
        display: none;

}
#DataTables_Table_1_info{
            display: none;

}
#DataTables_Table_2_info{
            display: none;

}
#DataTables_Table_3_info{
            display: none;

}
#DataTables_Table_4_info{
            display: none;

}
#DataTables_Table_5_info{
            display: none;

}
#links{
    display: block;
}
.navbar-vertical-aside-has-menu.active {
    background-color: #1F439A; /* لون داكن عند الفتح */
}

.navbar-vertical-aside-has-menu .nav-sub .nav-item {
    background-color: #4A5DC5; /* لون أزرق للعناصر الفرعية */
}
.navbar-vertical-aside .navbar-brand-wrapper {
    display: -ms-flexbox;
    display: flex;
    -ms-flex-align: center;
    align-items: center;
    height: 6rem;
    font-size: 1.5rem;
    padding-inline-end: 1.75rem;
    padding-inline-start: 1.75rem;
}
.text-logo{
        font-size: 1rem;

}
.ss{
    color: #f8be1c;
    padding-right: 5px;
}


</style>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>



<div id="sidebarMain" class="d-none">
    <aside class="aside-back js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered pb-4">
        <div class="navbar-vertical-container text-capitalize">
            <div class="navbar-vertical-footer-offset">
                <div class="navbar-brand-wrapper justify-content-between nav-brand-back side-logo">
                    <!-- Logo -->
                    @php($shop_logo=\App\Models\BusinessSetting::where(['key'=>'shop_name'])->first()->value)
                    <a class="navbar-brand " href="{{route('admin.dashboard')}}" aria-label="Front">
                     <h1 class="pt-1 ps-5 text-white text-logo" style="font-size:2rem;">نظام <span class="ss">Novoo</span></h1>
                    </a>
                    <!-- End Logo -->
                    <!-- Navbar Vertical Toggle -->
                    <button type="button"
                            class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs text-white">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

                <!-- Content -->
                <div class="navbar-vertical-content">
                    <ul class="navbar-nav navbar-nav-lg nav-tabs">
                        <!-- Dashboards -->

 <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/dashboard') ? 'show' : '' }}">
    <a class="js-navbar-vertical-aside-menu-link nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}"
       href="{{ route('admin.dashboard') }}"
       title="{{ \App\CPU\translate('dashboards') }}">
        <i class="tio-home-vs-1-outlined nav-icon"></i>
        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate ">
            {{ \App\CPU\translate('لوحة التحكم') }}
        </span>
    </a>
</li>

    <!-- Optionally, handle the case where the user is not authenticated or doesn't have dashboard access -->
    <li class="navbar-vertical-aside-has-menu">
        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
            <i class="tio-wallet nav-icon"></i>
            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                {{ \App\CPU\translate('نظام المحاسبة المتكامل') }}
            </span>
        </a>
        <ul class="js-navbar-vertical-aside-submenu nav nav-sub 
            {{ 
                Request::is('admin/account') || 
                Request::is('admin/storages/list') || 
                Request::is('admin/account/add') || 
                Request::is('admin/account/list') || 
                Request::is('admin/account/add-payable') || 
                Request::is('admin/account/add-expense/Expense') || 
                Request::is('admin/tax/list/today') || 
                Request::is('admin/account/add-income') ||
                Request::is('admin/account/add-transfer') ||
                Request::is('admin/account/list-transection') ||
                                Request::is('admin/account/listkoyod-transection') ||
                 Request::is('admin/storages/indextree') ||
                                  Request::is('admin/account/add-expense/100')||
                                  Request::is('admin/account/add-expense/200')||
Request::is('admin/account/add-expense')||
                                Request::is('admin/costcenter*') 

                ? 'd-block' 
                : '' 
            }}">
                        <li class="nav-item {{ Request::is('admin/costcenter*') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.costcenter.add') }}" title="{{ \App\CPU\translate('list_of_storages') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{ \App\CPU\translate('مراكز التكلفة') }}</span>
                </a>
            </li>
            <!-- Menu items -->
            <!--<li class="nav-item {{ Request::is('admin/storages/list') ? 'active' : '' }}">-->
            <!--    <a class="nav-link" href="{{ route('admin.storage.list') }}" title="{{ \App\CPU\translate('list_of_storages') }}">-->
            <!--        <span class="tio-circle nav-indicator-icon"></span>-->
            <!--        <span class="text-truncate i">{{ \App\CPU\translate('قائمة الفئات الحسابية الرئيسية') }}</span>-->
            <!--    </a>-->
            <!--</li>-->
         <li class="nav-item {{ Request::is('admin/storages/indextree') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.storage.indextree') }}" title="{{ \App\CPU\translate('list_of_storages') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{ \App\CPU\translate('شجرة   الرئيسية') }}</span>
                </a>
            </li>

            <li class="nav-item {{Request::is('admin/account/add')?'active':''}}">
                <a class="nav-link " href="{{route('admin.account.add')}}"
                   title="{{\App\CPU\translate('إضافة دليل محاسبي جديد')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('إضافة دليل محاسبي رئيسي')}}</span>
                </a>
            </li>

            <li class="nav-item {{Request::is('admin/account/list')?'active':''}}">
                <a class="nav-link " href="{{route('admin.account.list')}}"
                   title="{{\App\CPU\translate('أرصدة الحسابات')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('شجرة الحسابات')}}</span>
                </a>
            </li>
            <li class="nav-item {{Request::is('admin/account/listkoyod-transection')?'active':''}}">
                <a class="nav-link " href="{{route('admin.account.listkoyod-transection')}}"
                   title="{{\App\CPU\translate('قائمة المعاملات')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('القيود اليومية')}}</span>
                </a>
            </li>

            <li class="nav-item {{Request::is('admin/account/add-payable')?'active':''}}">
                <a class="nav-link" href="{{route('admin.account.add-payable')}}"
                   title="{{\App\CPU\translate('الأرصدة الأفتتاحية')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('أرصدة ألافتتاحية')}}</span>
                </a>
            </li>

            <!--<li class="nav-item {{Request::is('admin/account/add-expense')?'active':''}}">-->
            <!--    <a class="nav-link" href="{{ route('admin.account.addExpense') }}">-->
            <!--        <span class="tio-circle nav-indicator-icon"></span>-->
            <!--        <span class="text-truncate i">{{\App\CPU\translate('إضافة بنود   مصروفات ')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <li class="nav-item {{Request::is('admin/account/add-expense/100')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.add-expense', ['type' => '100']) }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('إضافة سند صرف')}}</span>
                </a>
            </li>

            <li class="nav-item {{Request::is('admin/account/add-expense/200')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.add-expense', ['type' => '200']) }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('إضافة سند قبض')}}</span>
                </a>
            </li>

            <!--<li class="nav-item {{Request::is('admin/account/add-income')?'active':''}}">-->
            <!--    <a class="nav-link " href="{{route('admin.account.add-income')}}"-->
            <!--       title="{{\App\CPU\translate('إضافة دخل جديد')}}">-->
            <!--        <span class="tio-circle nav-indicator-icon"></span>-->
            <!--        <span class="text-truncate i">{{\App\CPU\translate('تقرير إيرادات ')}}</span>-->
            <!--    </a>-->
            <!--</li>-->

            <li class="nav-item {{Request::is('admin/account/add-transfer')?'active':''}}">
                <a class="nav-link " href="{{route('admin.account.add-transfer')}}"
                   title="{{\App\CPU\translate('إضافة قيسد يومي جديد')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('إضافة قيد يدوي')}}</span>
                </a>
            </li>

            <!--<li class="nav-item {{ Request::is('admin/tax/list/today') ? 'active' : '' }}">-->
            <!--    <a class="nav-link" href="{{ route('admin.taxe.listallbox') }}" title="{{ \App\CPU\translate('list_of_taxes') }}">-->
            <!--        <span class="tio-circle nav-indicator-icon"></span>-->
            <!--        <span class="text-truncate i">{{ \App\CPU\translate('قائمة  الدخل ') }}</span>-->
            <!--    </a>-->
            <!--</li>-->

          

            <li class="nav-item {{Request::is('admin/account/list-transection')?'active':''}}">
                <a class="nav-link " href="{{route('admin.account.list-transection')}}"
                   title="{{\App\CPU\translate('قائمة المعاملات')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('كشف حساب')}}</span>
                </a>
            </li>
        </ul>
    </li>
  <li class="navbar-vertical-aside-has-menu">
        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
            <i class="tio-wallet nav-icon"></i>
            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                {{ \App\CPU\translate('قسم الاصول الثابتة') }}
            </span>
        </a>
        <ul class="js-navbar-vertical-aside-submenu nav nav-sub 
            {{ 

                 Request::is('admin/account/add-expense/2')|| Request::is('admin/depreciation')|| Request::is('admin/assets*')|| Request::is('admin/maintenance_logs*')

                ? 'd-block' 
                : '' 
            }}">
                <li class="nav-item {{Request::is('admin/assets')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.depreciation.index') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('الأصول الثابتة')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/account/add-expense/2')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.add-expense', ['type' => '2']) }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('إَضافة  أصل ثابت')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/depreciation')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.depreciation.show') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('إهلاك أصل ثابت')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/maintenance_logs/create')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.maintenance_logs.create') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('جدولة صيانة أصل ثابت')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/maintenance_logs')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.maintenance_logs.index') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('صيانة أصل ثابت')}}</span>
                </a>
            </li>
</ul>
</li>
                       <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
            <i class="tio-report nav-icon"></i>
            <span  class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ \App\CPU\translate('قسم التقارير المالية') }}</span>
        </a>
<ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/account/list-expense')||Request::is('admin/account/list-expense')||Request::is('admin/account/list-expense/2')||Request::is('admin/account/list-expense/200')||Request::is('admin/account/list-expense/100')||Request::is('admin/account/list-transfer')||Request::is('admin/reports/balance-sheet')||Request::is('admin/reports/indexOperating')||Request::is('admin/reports/indexTrialBalance')||Request::is('admin/reports/IncomeStatement')||Request::is('admin/reports/ageing-receivables')||Request::is('admin/reports/ageing-receivables-suppliers')||Request::is('admin/reports/expense-cost-centers')?'d-block':''}}">    

    <!--<li class="nav-item {{Request::is('admin/account/list-expense')?'active':''}}">-->
    <!--            <a class="nav-link" href="{{ route('admin.account.listExpense') }}">-->
    <!--                <span class="tio-circle nav-indicator-icon"></span>-->
    <!--                <span class="text-truncate">{{\App\CPU\translate('تقرير بنود مصروفات ')}}</span>-->
    <!--            </a>-->
    <!--        </li>-->
              <li class="nav-item {{Request::is('admin/account/list-expense/2')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.list-expense', ['type' => '2']) }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير أصول ثابتة')}}</span>
                </a>
            </li>
                   <li class="nav-item {{Request::is('admin/account/list-expense/100')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.list-expense', ['type' => '100']) }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير سندات صرف')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/account/list-expense/200')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.list-expense', ['type' => '200']) }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير سندات قبض')}}</span>
                </a>
            </li>
                <li class="nav-item {{Request::is('admin/account/list-transfer')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.account.list-transfer') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير القيود اليدوية')}}</span>
                </a>
            </li>
              <li class="nav-item {{Request::is('admin/reports/balance-sheet')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.balance-sheet') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير الميزانية العمومية')}}</span>
                </a>
            </li>
             <li class="nav-item {{Request::is('admin/reports/indexOperating')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.indexOperating') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير  التدفقات النقدية')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/reports/indexTrialBalance')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.indexTrialBalance') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير  ميزان المراجعة')}}</span>
                </a>
            </li>
            <li class="nav-item {{Request::is('admin/reports/IncomeStatement')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.IncomeStatement') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير  قائمة الدخل')}}</span>
                </a>
            </li>
              <li class="nav-item {{Request::is('admin/reports/ageing-receivables')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.AgeingReceivables') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير   اعمار ديون العملاء')}}</span>
                </a>
            </li>
                 <li class="nav-item {{Request::is('admin/reports/ageing-receivables-suppliers')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.suppliersIndex') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير   اعمار ديون الموردين')}}</span>
                </a>
            </li>
               <li class="nav-item {{Request::is('admin/reports/expense-cost-centers')?'active':''}}">
                <a class="nav-link" href="{{ route('admin.expenseCostCentersReport') }}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{\App\CPU\translate('تقرير مراكز التكلفة')}}</span>
                </a>
            </li>

        
    </ul>
</li>

 <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/admin/notifications') ? 'active' : '' }}">

            <a class="nav-link" href="{{route('admin.admin.notifications.listItems')}}"
               title="{{\App\CPU\translate('list_stock')}}">
        <i class="tio-notifications nav-icon"></i>
                <span class="text-truncate">{{\App\CPU\translate('الاشعارات')}}</span>@if(\App\Models\Order::where('notification', 1)->get()->count() > 0)
                <span class="badge badge-pill badge-danger ml-3" style="font-size:12px">{{ \App\Models\Order::where('notification', 1)->get()->count() +\App\Models\ReserveProduct::where('notification', 1)->get()->count() +\App\Models\TransactionSeller::get()->count() +\App\Models\HistoryInstallment::where('notification', 1)->get()->count()}}</span>
            @endif
            </a>
            </li>



<li class="navbar-vertical-aside-has-menu {{ Request::is('admin/admin/pos*')|| Request::is('admin/pos/pos/7') ? 'd-block' : '' }}">
    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" data-bs-toggle="collapse" data-bs-target="#salesDropdownContent" aria-expanded="{{ Request::is('admin/admin/pos*') ? 'true' : 'false' }}">
        <i class="tio-shopping nav-icon"></i>
        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
            {{ \App\CPU\translate('انشاء مبيعات') }}
        </span>
    </a>

    <ul class="js-navbar-vertical-aside-submenu nav nav-sub collapse {{ Request::is('admin/admin/pos*')|| Request::is('admin/pos/pos/7')|| Request::is('admin/purchase_invoice/create')|| Request::is('admin/pos/pos/24') ? 'd-block' : '' }}" id="salesDropdownContent">
             <li class="nav-item {{ Request::is('admin/pos/1') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pos.index', ['type' => 1]) }}" title="{{ \App\CPU\translate('list_stock') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{ \App\CPU\translate('كاشير') }}</span>
            </a>
        </li> 
        <li class="nav-item {{ Request::is('admin/pos/4') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pos.index', ['type' => 4]) }}" title="{{ \App\CPU\translate('list_stock') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{ \App\CPU\translate('مبيعات') }}</span>
            </a>
        </li>
        <li class="nav-item {{ Request::is('admin/pos/pos/7') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pos.index', ['type' => 7]) }}" title="{{ \App\CPU\translate('list_stock') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{ \App\CPU\translate('مرتجع مبيعات') }}</span>
            </a>
        </li>
        <!--    <li class="nav-item {{ Request::is('admin/admin/pos*') && request('type') == 12 ? 'active' : '' }}">-->
        <!--    <a class="nav-link" href="{{ route('admin.pos.index', ['type' => 12]) }}" title="{{ \App\CPU\translate('list_stock') }}">-->
        <!--        <span class="tio-circle nav-indicator-icon"></span>-->
        <!--        <span class="text-truncate i">{{ \App\CPU\translate('مشتريات') }}</span>-->
        <!--    </a>-->
        <!--</li>-->
            <li class="nav-item {{ Request::is('admin/purchase_invoice/create') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.purchase_invoice.create') }}" title="{{ \App\CPU\translate('list_stock') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{ \App\CPU\translate('مشتريات') }}</span>
            </a>
        </li>
            <li class="nav-item {{ Request::is('admin/pos/pos/24') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pos.index', ['type' => 24]) }}" title="{{ \App\CPU\translate('list_stock') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{ \App\CPU\translate('مرتجع مشتريات') }}</span>
            </a>
        </li>
    </ul>
</li>

                        <!-- Pos Pages -->
                             <li class="navbar-vertical-aside-has-menu">
    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
        <i class="fa-solid fa-file-invoice nav-icon"></i> 
        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('الفواتير')}}</span>
    </a>
    <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{
 
        Request::is('admin/admin/TransactionSeller') || 
        Request::is('admin/pos/orders') || 
        Request::is('admin/pos/refunds') || 
        Request::is('admin/pos/sample') || 
        Request::is('admin/pos/donations') || 
        Request::is('admin/pos/installments') || 
        Request::is('admin/pos/reservations/4/all') || 
        Request::is('admin/pos/reservations/7/all') || 
        Request::is('admin/pos/stocks') 
        ? 'd-block' 
        : '' 
    }}">
        <li class="nav-item {{Request::is('admin/admin/TransactionSeller')?'active':''}}">
            <a class="nav-link " href="{{route('admin.TransactionSeller.index')}}"
               title="{{\App\CPU\translate('list_of_admin')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{\App\CPU\translate('تحويلات المناديب')}}</span>
                <span class="badge badge-success ml-2">{{\App\Models\TransactionSeller::get()->count()}} </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/orders')?'active':''}}">
            <a class="nav-link " href="{{route('admin.pos.orders')}}"
               title="{{\App\CPU\translate('orders')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{\App\CPU\translate('المبيعات')}}
                    <span class="badge badge-success ml-2">{{\App\Models\Order::where('type', 4)->get()->count()}}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/refunds')?'active':''}}">
            <a class="nav-link " href="{{route('admin.pos.refunds')}}"
               title="{{\App\CPU\translate('refunds')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate i">{{\App\CPU\translate('المرتجعات المبيعات')}}
                    <span class="badge badge-success ml-2">{{\App\Models\Order::where('type', 7)->get()->count()}}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/sample')?'active':''}}">
            <a class="nav-link " href="{{route('admin.pos.sample')}}"
               title="{{\App\CPU\translate('sample')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{\App\CPU\translate('مشتريات')}}
                    <span class="badge badge-success ml-2">{{\App\Models\Order::where('type', 12)->get()->count()}}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/donations')?'active':''}}">
            <a class="nav-link " href="{{route('admin.pos.donations')}}"
               title="{{\App\CPU\translate('donations')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{\App\CPU\translate('مرتجع مشتريات')}}
                    <span class="badge badge-success ml-2">{{\App\Models\Order::where('type', 24)->get()->count()}}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/installments')?'active':''}}">
            <a class="nav-link " href="{{route('admin.pos.installments')}}"
               title="{{\App\CPU\translate('installments')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{\App\CPU\translate('التحصيلات')}}
                    <span class="badge badge-success ml-2">{{\App\Models\Installment::get()->count()}}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/reservations/4/all')?'active':''}}">
            <a class="nav-link" href="{{ route('admin.pos.reservations', ['type' => 4, 'active' => 'all']) }}" title="{{ \App\CPU\translate('reservations') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{ \App\CPU\translate('الحجوزات') }}
                    <span class="badge badge-success ml-2">{{ \App\Models\ReserveProduct::where('type', 4)->count() }}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{ Request::is('admin/pos/reservations/7/all') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pos.reservations', ['type' => 7, 'active' => 'all']) }}" title="{{ \App\CPU\translate('reservations') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{ \App\CPU\translate('رد الحجوزات') }}
                    <span class="badge badge-success ml-2">{{ \App\Models\ReserveProduct::where('type', 7)->count() }}</span>
                </span>
            </a>
        </li>
        <li class="nav-item {{Request::is('admin/pos/stocks')?'active':''}}">
            <a class="nav-link " href="{{route('admin.pos.stocks')}}"
               title="{{\App\CPU\translate('stock_travels')}}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{\App\CPU\translate('رحلات العربيات')}}
                    <span class="badge badge-success ml-2">{{\App\Models\StockOrder::count()}}</span>
                </span>
            </a>
        </li>
    </ul>
</li>


      
      
                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-shopping nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('المخازن')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/transfer*')||Request::is('admin/vehicle-stock*')||Request::is('admin/vehicle-stock/create')||Request::is('admin/stockbatch')||Request::is('/admin/transfer')?'d-block':''}}">
                                   <li class="nav-item {{Request::is('admin/transfer')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.transfer.index')}}"
                                       title="{{\App\CPU\translate('list_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة التحويلات')}}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/stockbatch')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.stockbatch.index')}}"
                                       title="{{\App\CPU\translate('list_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة المنتجات داخل المخازن')}}</span>
                                    </a>
                                </li>
                               
                                <li class="nav-item {{Request::is('admin/transfer/create')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.transfer.create')}}"
                                       title="{{\App\CPU\translate('add_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('امر تحويل مخزني')}}</span>
                                    </a>
                                </li>

                       
                        </li>

                            </ul>

   <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-shopping nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('التسويات')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/inventory_adjustments/create')||Request::is('admin/inventory_adjustments*')||Request::is('admin/inventory_adjustments/edit*')?'d-block':''}}">
                                   <li class="nav-item {{Request::is('admin/inventory_adjustments')||Request::is('admin/inventory_adjustments/edit*')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.inventory_adjustments.index')}}"
                                       title="{{\App\CPU\translate('list_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة الجرد')}}</span>
                                    </a>
                                </li>
                                
                            
                               
                                <li class="nav-item {{Request::is('admin/inventory_adjustments/create')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.inventory_adjustments.create')}}"
                                       title="{{\App\CPU\translate('add_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('امر جرد مخزني')}}</span>
                                    </a>
                                </li>

                       
                        </li>

                            </ul>
                        <!-- End Dashboards -->

                        <!-- Pos End Pages -->

                        

                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-shopping nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('المستودعات')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/stores*')||Request::is('admin/vehicle-stock*')||Request::is('admin/vehicle-stock/create')?'d-block':''}}">
                                   <li class="nav-item {{Request::is('admin/stores')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.stores.index')}}"
                                       title="{{\App\CPU\translate('list_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة المستودعات')}}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/vehicle-stock')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.stock.index')}}"
                                       title="{{\App\CPU\translate('list_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة المنتجات داخل المستودعات')}}</span>
                                    </a>
                                </li>
                               
                                <li class="nav-item {{Request::is('admin/vehicle-stock/create')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.stock.create')}}"
                                       title="{{\App\CPU\translate('add_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('امر صرف مخزني')}}</span>
                                    </a>
                                </li>

                                <!--  <li class="nav-item {{Request::is('admin/vehicle-stock/vehicles')?'active':''}}">-->
                                <!--    <a class="nav-link " href="{{route('admin.stock.vehicles')}}"-->
                                <!--       title="{{\App\CPU\translate('vehicles_stocks')}}">-->
                                <!--        <span class="tio-circle nav-indicator-icon"></span>-->
                                <!--        <span class="text-truncate">{{\App\CPU\translate('امر جرد مخزني')}}</span>-->
                                <!--    </a>-->
                                <!--</li>-->
                        </li>

                            </ul>
                        </li>

<li class="navbar-vertical-aside-has-menu">
    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
<i class="fas fa-warehouse nav-icon"></i>
<span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ \App\CPU\translate('امر توريد او مرتجع') }}</span>
    </a>
    <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{ Request::is('admin/pos/reservations_notification/4/1')||Request::is('admin/pos/reservations_notification/7/1')||Request::is('admin/pos/reservations_notification/3/2')  ? 'd-block' : '' }}">
        <li class="nav-item {{ Request::is('admin/pos/reservations_notification/4/1') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.pos.reservation_list_notification', ['type' => 4, 'active' => 1]) }}"
               title="{{ \App\CPU\translate('list_stock') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{ \App\CPU\translate('امر توريد بضاعة') }}</span>
                <span class="badge badge-success ml-2">{{ \App\Models\ReserveProduct::where('type', 4)->where('active',1)->count() }}</span>
            </a>
        </li>
        <li class="nav-item {{ Request::is('admin/pos/reservations_notification/7/1') ? 'active' : '' }}">
<a class="nav-link" href="{{ route('admin.pos.reservation_list_notification', ['type' => 7, 'active' => 1]) }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{ \App\CPU\translate('امر مرتجع بضاعة') }}</span>
                <span class="badge badge-success ml-2">{{ \App\Models\ReserveProduct::where('type', 7)->where('active',1)->count() }}</span>
            </a>
        </li>
         <li class="nav-item {{ Request::is('admin/pos/reservations_notification/3/2') ? 'active' : '' }}">
<a class="nav-link" href="{{ route('admin.pos.reservation_list_notification', ['type' => 3, 'active' => 2]) }}">
                <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{ \App\CPU\translate('اوامر الصرف بضاعة') }}</span>
                <span class="badge badge-success ml-2">{{ \App\Models\ReserveProduct::where('type', 3)->where('active',2)->count() }}</span>
            </a>
        </li>
    </ul>
</li>

                          
                        
                        <!-- Stores Pages-->
         
                        <!-- Pos End Pages -->

                        <!-- category Pages -->

                       <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/category/add') ? 'active' : '' }}">

                                    <a class="nav-link " href="{{route('admin.category.add')}}"
                                       title="{{\App\CPU\translate('add_new_category')}}">
                                <i class="tio-category nav-icon"></i>
                                        <span class="text-truncate">{{\App\CPU\translate('الاقسام')}}</span>
                                    </a>
                                </li>

                                {{-- <li class="nav-item {{Request::is('admin/category/add-sub-category')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.category.add-sub-category')}}"
                                       title="{{\App\CPU\translate('add_new_sub_category')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('sub_category')}}</span>
                                    </a>
                                </li> --}}
                        </li>

                        <!-- category End Pages -->

                                  <li class="navbar-vertical-aside-has-menu ">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            >
                                <i class="tio-category nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('الوحدات منتجات')}}</span>
                            </a>
                        <!--{{-- <!-- Brand -->-->
                        <!--<li class="navbar-vertical-aside-has-menu {{Request::is('admin/unit*')?'active':''}}">-->
                        <!--    <a class="js-navbar-vertical-aside-menu-link nav-link"-->
                        <!--       href="{{route('admin.brand.add')}}"-->
                        <!--    >-->
                        <!--        <i class="tio-star nav-icon"></i>-->
                        <!--        <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">-->
                        <!--            {{\App\CPU\translate('brand')}}-->
                        <!--        </span>-->
                        <!--    </a>-->
                        <!--</li>-->
                        <!--Brand end --> --}}-->
                        <!-- unit -->
                                                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/unit*')?'d-block':''}}">

                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/unit/index/2')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
href="{{ route('admin.unit.index', ['units' => 2]) }}"
                            >
                <span class="tio-circle nav-indicator-icon"></span>
<span class="text-truncate">                                    {{\App\CPU\translate(' وحدات قياس الكبري')}}
                                </span>
                            </a>
                        </li>
                         <li class="navbar-vertical-aside-has-menu {{Request::is('admin/unit/index/1')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
href="{{ route('admin.unit.index', ['units' => 1]) }}"
                            >
                <span class="tio-circle nav-indicator-icon"></span>
<span class="text-truncate">                                    {{\App\CPU\translate(' وحدات قياس الصغري')}}
                                </span>
                            </a>
                        </li>
                                </ul>
                        </li>
                        <!--unit end -->

                         <li class="navbar-vertical-aside-has-menu {{Request::is('admin/tax/list')?'active':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
href="{{ route('admin.taxe.list') }}"
                            >
<span class="fas fa-receipt nav-indicator-icon"></span>  <!-- أيقونة الفاتورة -->
<span class="text-truncate">                                    {{\App\CPU\translate('انواع الضرايب')}}
                                </span>
                            </a>
                        </li>

                        <!-- Product Pages -->

                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-premium-outlined nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('المنتجات')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/product/list') ||Request::is('admin/product/add')||Request::is('admin/product/addexpire')||Request::is('admin/product/listexpireinvoice')||Request::is('admin/product/bulk-import')||Request::is('admin/product/edit*')||Request::is('admin/product/barcode-generate*')||Request::is('admin/product/listProductsByOrderType*')?'d-block':''}}">
                                <li class="nav-item {{Request::is('admin/product/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.product.add')}}"
                                       title="{{\App\CPU\translate('add_new_product')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة منتج')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('admin/product/list')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.product.list')}}"
                                       title="{{\App\CPU\translate('list_of_products')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة المنتجات')}}</span>
                                    </a>
                                </li>
                                  <li class="nav-item {{Request::is('admin/product/addexpire')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.product.addexpire')}}"
                                       title="{{\App\CPU\translate('add_new_product')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة منتج هالك')}}</span>
                                    </a>
                                </li>
                                   <li class="nav-item {{Request::is('admin/product/listexpireinvoice')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.product.listexpireinvoice')}}"
                                       title="{{\App\CPU\translate('listexpireinvoice')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة اوامر الهالك')}}</span>
                                    </a>
                                </li>
                      
                                <li class="nav-item {{Request::is('admin/product/bulk-import')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.product.bulk-import')}}"
                                       title="{{\App\CPU\translate('bulk_import')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('استيراد اكسل')}}</span>
                                    </a>
                                </li>
                                <li class="nav-item {{Request::is('admin/product/bulk-export')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.product.bulk-export')}}"
                                       title="{{\App\CPU\translate('bulk_export')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اصدار اكسل')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Product End Pages -->
                        <!--<li class="nav-item">-->
                        <!--    <small-->
                        <!--        class="nav-subtitle">{{\App\CPU\translate('قسم البيزنس')}}</small>-->
                        <!--    <small class="tio-more-horizontal nav-subtitle-replacer"></small>-->
                        <!--</li>-->
                        <!-- Coupon End Pages -->
                        <!--<li class="navbar-vertical-aside-has-menu {{Request::is('admin/coupon*')?'active':''}}">-->
                        <!--    <a class="js-navbar-vertical-aside-menu-link nav-link"-->
                        <!--       href="{{route('admin.coupon.add-new')}}">-->
                        <!--        <i class="tio-gift nav-icon"></i>-->
                        <!--        <span-->
                        <!--            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('كوبونات الخصومات')}}</span>-->
                        <!--    </a>-->
                        <!--</li>-->

                       <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
            <i class="tio-report nav-icon"></i>
            <span  class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{ \App\CPU\translate('قسم التقارير') }}</span>
        </a>
<ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/product/getreportProducts') ||Request::is('admin/product/getreportProductsPurchase')||Request::is('admin/tax/listalltodaynew')||Request::is('admin/product/getreportProductsSales*')||Request::is('admin/tax/list/tax')||Request::is('admin/tax/list/box')||Request::is('admin/tax/list/listalltodaybyseller')||Request::is('admin/product/getreportMainStock')||Request::is('admin/product/getReportProductsAllStock')||Request::is('admin/product/listreportexpire')||Request::is('admin/stock/stock-limit')|| Request::is('admin/productsunlike')?'d-block':''}}">    

    <!--<li class="nav-item {{Request::is('admin/account/list-expense')?'active':''}}">-->
    <!--            <a class="nav-link" href="{{ route('admin.account.listExpense') }}">-->
    <!--                <span class="tio-circle nav-indicator-icon"></span>-->
    <!--                <span class="text-truncate">{{\App\CPU\translate('تقرير بنود مصروفات ')}}</span>-->
    <!--            </a>-->
    <!--        </li>-->
           
<li class="nav-item {{ Request::is('admin/product/getreportProducts') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.product.getreportProducts') }}" title="{{ \App\CPU\translate('getreportProducts') }}">
                             <span class="tio-circle nav-indicator-icon"></span>

                <span class="text-truncate">{{ \App\CPU\translate('كشف المنتجات المباعة') }}</span>
            </a>
        </li>
         <li class="nav-item {{ Request::is('admin/product/getreportProductsPurchase') ? 'active' : '' }}">
             
            <a class="nav-link" href="{{ route('admin.product.getreportProductsPurchase') }}" title="{{ \App\CPU\translate('getreportProductsPurchase') }}">
                                             <span class="tio-circle nav-indicator-icon"></span>

                <span class="text-truncate">{{ \App\CPU\translate('كشف  المشتريات') }}</span>
            </a>
        </li>
            <li class="nav-item {{ Request::is('admin/tax/listalltodaynew') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.taxe.listalltodaynew') }}" title="{{ \App\CPU\translate('listalltodaynew') }}">
                <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate">{{ \App\CPU\translate('تقرير نقاط البيع') }}</span>
                </a>
            </li>
         <li class="nav-item {{ Request::is('admin/product/getreportProductsSales/1') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.product.getreportProductsSales', ['type' => 1]) }}" title="{{ \App\CPU\translate('getreportProductsSales') }}">
                                <span class="tio-circle nav-indicator-icon"></span>

                <span class="text-truncate">{{ \App\CPU\translate('هامش الربح والخسارة') }}</span>
            </a>
        </li>
        <li class="nav-item {{ Request::is('admin/product/getreportProductsSales/0') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('admin.product.getreportProductsSales', ['type' => 0]) }}" title="{{ \App\CPU\translate('getreportProductsSales') }}">
                                                            <span class="tio-circle nav-indicator-icon"></span>

        <span class="text-truncate">{{ \App\CPU\translate('الكميات المباعة للمندوبين') }}</span>
    </a>
</li>
           <li class="nav-item {{ Request::is('admin/tax/list/tax') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.taxe.listall') }}" title="{{ \App\CPU\translate('list_of_taxes') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>

                    <span class="text-truncate">{{ \App\CPU\translate('قائمة  الضرائب ') }}</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('admin/tax/list/box') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.taxe.listalltoday') }}" title="{{ \App\CPU\translate('list_of_taxes') }}">
                                                    <span class="tio-circle nav-indicator-icon"></span>

                    <span class="text-truncate">{{ \App\CPU\translate('قائمة  الصندوق ') }}</span>
                </a>
            </li>
            <li class="nav-item {{ Request::is('admin/tax/list/listalltodaybyseller') ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('admin.taxe.listalltodaybyseller') }}" title="{{ \App\CPU\translate('list_of_taxes') }}">
                                                                 <span class="tio-circle nav-indicator-icon"></span>

                    <span class="text-truncate">{{ \App\CPU\translate('قائمة  الصندوق اليومي ') }}</span>
                </a>
            </li>
          <li class="nav-item {{ Request::is('admin/product/getreportMainStock') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.product.getreportMainStock') }}" title="{{ \App\CPU\translate('getreportMainStock') }}">
                                                                                 <span class="tio-circle nav-indicator-icon"></span>

                <span class="text-truncate">{{ \App\CPU\translate('كشف المستودع الرئيسي') }}</span>
            </a>
        </li>
        <!-- <li class="nav-item {{ Request::is('admin/product/getReportProductsAllStock') ? 'active' : '' }}">-->
        <!--    <a class="nav-link" href="{{ route('admin.product.getReportProductsAllStock') }}" title="{{ \App\CPU\translate('getReportProductsAllStock') }}">-->
        <!--                                                                                         <span class="tio-circle nav-indicator-icon"></span>-->

        <!--        <span class="text-truncate">{{ \App\CPU\translate('كشف المستودعات') }}</span>-->
        <!--    </a>-->
        <!--</li>-->
        <li class="nav-item {{ Request::is('admin/product/listreportexpire') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.product.listreportexpire') }}" title="{{ \App\CPU\translate('list_of_products') }}">
                                                                                                 <span class="tio-circle nav-indicator-icon"></span>

                <span class="text-truncate">{{ \App\CPU\translate('كشف الصلاحية') }}</span>
            </a>
        </li>

        <li class="nav-item {{ Request::is('admin/stock/stock-limit') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.stock.stock-limit') }}">
                                                                                                 <span class="tio-circle nav-indicator-icon"></span>
                <span class="text-truncate">{{ \App\CPU\translate('كشف نواقص') }}</span>
            </a>
        </li>

        <li class="nav-item {{ Request::is('admin/productsunlike') ? 'active' : '' }}">
            <a class="nav-link" href="{{ route('admin.ordernotification.Productunlike') }}" title="{{ \App\CPU\translate('list_of_products') }}">
                <span class="tio-circle nav-indicator-icon"></span>

                <span class="text-truncate">{{ \App\CPU\translate('كشف الركود') }}</span>
            </a>
        </li>
         
        
    </ul>
</li>

                       
                        <!-- Admin Pages -->
                                      <li class="navbar-vertical-aside-has-menu {{Request::is('admin/regions/list') ||Request::is('admin/regions/edit*')  ? 'active':''}}">

                                    <a class="nav-link " href="{{route('admin.regions.list')}}"
                                       title="{{\App\CPU\translate('regions')}}">
                                <i class="tio-city nav-icon"></i>
                                        <span class="text-truncate">{{\App\CPU\translate('المناطق')}}</span>
                                    </a>
                                </li>



                                         
                        <!-- Coupon End Pages -->

                        <!-- Account start pages -->
            
                        <!-- Account End pages -->

                      
                        <!-- Customer Pages -->
                        <li class="navbar-vertical-aside-has-menu {{Request::is('admin/customer*')||Request::is('admin/category/add-special-category')||Request::is('/admin/customer/transaction-list/*')?'d-blcok':''}}">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            >
                                <i class="tio-poi-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('العملاء')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/customer*')||Request::is('admin/category/add-special-category')?'d-block':''}}">
                                <li class="nav-item {{Request::is('admin/customer/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.customer.add')}}"
                                       title="{{\App\CPU\translate('add_new_customer')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة عميل')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('admin/customer/list')||Request::is('/admin/customer/transaction-list/*')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.customer.list')}}"
                                       title="{{\App\CPU\translate('list_of_customers')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة العملاء')}}</span>
                                    </a>
                                </li>
                                <li class="navbar-vertical-aside-has-menu {{Request::is('admin/special*')?'active':''}}">
                                <li class="nav-item {{Request::is('admin/category/add-special-category')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.category.indexspecial')}}"
                                       title="{{\App\CPU\translate('add_new_category')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('التخصصات')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>


                        <!-- Supplier Pages -->
                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            >
                                <i class="tio-users-switch nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('الموردين')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/supplier*')?'d-block':''}}">
                                <li class="nav-item {{Request::is('admin/supplier/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.supplier.add')}}"
                                       title="{{\App\CPU\translate('add_new_supplier')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة مورد')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('admin/supplier/list')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.supplier.list')}}"
                                       title="{{\App\CPU\translate('list_of_suppliers')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة الموردين')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Supplier end Pages -->
                       


                      
                        <!-- Seller Pages -->
                        <li class="navbar-vertical-aside-has-menu ">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            >
<i class="fa-solid fa-truck-fast nav-icon"></i>
<span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('المناديب')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/seller*')||Request::is('admin/seller/prices')|| Request::is('admin/admin/showmap')||Request::is('admin/visitors') ?'d-block':''}}">
                                <li class="nav-item {{Request::is('admin/seller/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.seller.add')}}"
                                       title="{{\App\CPU\translate('add_new_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة مندوب')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('admin/seller/list') ||Request::is('admin/seller/prices*')||Request::is('admin/seller/edit*') || Request::is('admin/seller/prices/edit*')  ?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.seller.list')}}"
                                       title="{{\App\CPU\translate('list_of_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة مناديب')}}</span>
                                    </a>
                                </li>
                                 <li class="nav-item {{Request::is('admin/visitors/create')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.visitor.create')}}"
                                       title="{{\App\CPU\translate('add_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('تسجيل زيارات الشهر')}}</span>
                                    </a>
                                </li>
                                   <li class="nav-item {{Request::is('admin/visitors')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.visitor.index')}}"
                                       title="{{\App\CPU\translate('list_stock')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة الزيارات الشهر ')}}</span>
                                    </a>
                                </li>
                               
                                     <li class="nav-item {{Request::is('admin/admin/showmap')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.admin.showmap')}}"
                                       title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('خريطة المناديب')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Admin Pages -->
                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:">
                                <i class="tio-poi-user nav-icon"></i>
                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('قسم  موارد البشرية')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/staff/add')|| Request::is('admin/staff/list')|| Request::is('admin/staff/edit*')|| Request::is('admin/admin/salaries/createrating')||Request::is('admin/admin/developsellers*')||Request::is('admin/coursesellers/create')||Request::is('admin/coursesellers/list')||Request::is('admin/job_applicants/create')||Request::is('admin/coursesellers')||Request::is('admin/job_applicants*')||Request::is('admin/interview-evaluations*')||Request::is('admin/attendance*')||Request::is('admin/admin/salaries/create')||Request::is('admin/admin/salaries')?'d-block':''}}">
 <li class="nav-item {{Request::is('admin/staff/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.staff.add')}}"
                                       title="{{\App\CPU\translate('add_new_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة موظف')}}</span>
                                    </a>
                                </li>
                                 <li class="nav-item {{Request::is('admin/job_applicants/create')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.job_applicants.create')}}"
                                       title="{{\App\CPU\translate('add_new_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة طلب توظيف')}}</span>
                                    </a>
                                </li>
                                    <li class="nav-item {{Request::is('admin/job_applicants*')||Request::is('admin/interview-evaluations*') ?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.job_applicants.index')}}"
                                       title="{{\App\CPU\translate('add_new_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة طلبات توظيف')}}</span>
                                    </a>
                                </li>


                                <li class="nav-item {{Request::is('admin/staff/list')||Request::is('admin/staff/edit*') ?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.staff.list')}}"
                                       title="{{\App\CPU\translate('list_of_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة موظفين')}}</span>
                                    </a>
                                </li>
                                  <li class="nav-item {{Request::is('admin/attendance') ?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.attendance.index')}}"
                                       title="{{\App\CPU\translate('list_of_seller')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('سجلات الحضور والانصراف ')}}</span>
                                    </a>
                                </li>
                                    <li class="nav-item {{Request::is('admin/admin/salaries/attendance')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.salaries.createrating')}}"
                                       title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('تقييم موظف')}}</span>
                                    </a>
                                </li>
                                
                                   <li class="nav-item {{Request::is('admin/admin/developsellers/0')||Request::is('admin/admin/developsellers/create/0')?'active':''}}">
<a class="nav-link" href="{{ route('admin.developsellers.index', ['type' => 0]) }}" title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('تطوير موظف')}}</span>
                                    </a>
                                </li>
                                    <li class="nav-item {{Request::is('admin/coursesellers*')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.coursesellers.index')}}"
                                       title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('كورسات موظف')}}</span>
                                    </a>
                                </li>
                                    <li class="nav-item {{Request::is('admin/admin/developsellers/1')?'active':''}}">
<a class="nav-link" href="{{ route('admin.developsellers.index', ['type' => 1]) }}" title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('طلبات موظف')}}</span>
                                    </a>
                                </li>
                                   <li class="nav-item {{Request::is('admin/admin/developsellers/2')?'active':''}}">
<a class="nav-link" href="{{ route('admin.developsellers.index', ['type' => 2]) }}" title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('طلبات اجازة')}}</span>
                                    </a>
                                </li>
                                 <li class="nav-item {{Request::is('admin/admin/salaries/create')?'active':''}}">
                <a class="nav-link " href="{{route('admin.salaries.create')}}"
                   title="{{\App\CPU\translate('list_of_admin')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('دفع مرتب')}}</span>
                </a>
            </li>

            <li class="nav-item {{Request::is('admin/admin/salaries')?'active':''}}">
                <a class="nav-link " href="{{route('admin.salaries.index')}}"
                   title="{{\App\CPU\translate('list_of_admin')}}">
                    <span class="tio-circle nav-indicator-icon"></span>
                    <span class="text-truncate i">{{\App\CPU\translate('مراجعة المرتبات')}}</span>
                </a>
            </li>
  
                            </ul>
                        </li>
                        <!-- Seller end Pages -->

                        <!-- Admin Pages -->
                        <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            >
<i class="fa-solid fa-lock nav-icon"></i>                                <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('الادمن')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/admin/add')||Request::is('admin/admin/list')|| Request::is('admin/admin/edit*')?'d-block':''}}">
                                <li class="nav-item {{Request::is('admin/admin/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.admin.add')}}"
                                       title="{{\App\CPU\translate('add_new_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة ادمن')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('admin/admin/list')|| Request::is('admin/admin/edit*')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.admin.list')}}"
                                       title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة الادمن')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                              <li class="navbar-vertical-aside-has-menu">
                            <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            >
<i class="fa-solid fa-business-time nav-icon"></i>
                              <span
                                    class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">{{\App\CPU\translate('مواعيد العمل')}}</span>
                            </a>
                            <ul class="js-navbar-vertical-aside-submenu nav nav-sub {{Request::is('admin/shift/add')||Request::is('admin/shift/list')|| Request::is('admin/shift/edit*')?'d-block':''}}">
                                <li class="nav-item {{Request::is('admin/shift/add')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.shift.add')}}"
                                       title="{{\App\CPU\translate('add_new_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('اضافة مواعيد عمل')}}</span>
                                    </a>
                                </li>

                                <li class="nav-item {{Request::is('admin/shift/list')|| Request::is('admin/shift/edit*')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.shift.list')}}"
                                       title="{{\App\CPU\translate('list_of_admin')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('قائمة مواعيد العمل')}}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                       <li class="navbar-vertical-aside-has-menu {{ Request::is('admin/branch/add') ? 'active' : '' }}">

                                    <a class="nav-link " href="{{route('admin.branch.add')}}"
                                       title="{{\App\CPU\translate('add_new_category')}}">
<i class="fas fa-tree nav-icon"></i> <!-- FontAwesome -->
                                        <span class="text-truncate">{{\App\CPU\translate('الفروع')}}</span>
                                    </a>
                                </li>

                                {{-- <li class="nav-item {{Request::is('admin/category/add-sub-category')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.category.add-sub-category')}}"
                                       title="{{\App\CPU\translate('add_new_sub_category')}}">
                                        <span class="tio-circle nav-indicator-icon"></span>
                                        <span class="text-truncate">{{\App\CPU\translate('sub_category')}}</span>
                                    </a>
                                </li> --}}
                        </li>

                                <li class="navbar-vertical-aside-has-menu {{Request::is('admin/roles')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.role.index')}}"
                                    >
<i class="tio-users-switch nav-icon"></i> <!-- or any other suitable icon -->
                                        <span
                                            class="text-truncate">{{\App\CPU\translate('الصلاحيات')}}</span>
                                    </a>
                                </li>

                        <!-- Admin end Pages -->
    
                        <!-- Settings Start Pages -->

                     
                                <li class="nav-item {{Request::is('admin/business-settings/shop-setup')?'active':''}}">
                                    <a class="nav-link " href="{{route('admin.business-settings.shop-setup')}}"
                                    >
                                <i class="tio-settings nav-icon"></i>
                                        <span
                                            class="text-truncate">{{\App\CPU\translate('الشركة')}} {{\App\CPU\translate('تعديل الاعدادات')}}</span>
                                    </a>
                                </li>

                     
                    </ul>
                </div>
                <!-- End Content -->
            </div>
        </div>
    </aside>
</div>



@push('script_2')
    <script src={{asset("public/assets/admin/js/accounts.js")}}></script>
<script>
    $(document).ready(function () {
        $('.table').DataTable({
            "paging": false,
            "ordering": true,
            "info": false,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Arabic.json"
            }
        });
    });
    
</script>


@endpush
