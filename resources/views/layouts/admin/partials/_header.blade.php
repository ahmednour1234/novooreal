<style> 
#headerMain{
                              background-color:green;

  }
.badge{
    background-color: #f8be1c;
    color:white;
    
}
.close {
    float: right;
    font-size: 1.5rem;
    font-weight: 600;
    line-height: 1;
    color: black;
}

.close:hover {
    color: black;
}
.header-style {
    border-radius: 0px;
    top: 0px;

    background: #ffffff!important;
}
.nav-brand-back {
    background: #001B63 !important;
    color:white;
}
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

<div id="headerMain" class="d-none">
<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered header-style">
    <div class="navbar-nav-wrap">
        <!-- Logo Section -->
   
        <!-- End Logo Section -->

        <!-- Navbar Vertical Toggle -->
        
        <!-- End Navbar Vertical Toggle -->

        <!-- Search Bar -->
          <div class="navbar-nav-wrap-content-left">
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker close mr-3">
                <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip" data-placement="right" title="غلق"></i>
                <i class="tio-last-page  navbar-vertical-aside-toggle-full-align"
                   data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                   data-toggle="tooltip" data-placement="right" title="فتح"></i>
            </button>
        </div>
        <div class="navbar-nav-wrap-content-center">
            
        </div>
        
        <!-- End Search Bar -->

        <!-- Secondary Content -->
        <div class="navbar-nav-wrap-content-right">
            <ul class="navbar-nav align-items-center flex-row">
                <!-- Notifications -->
                <li class="nav-item">
                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary" href="javascript:;"
                           data-hs-unfold-options='{
                                "target": "#notificationDropdown",
                                "type": "css-animation"
                            }'>
                            @if(\App\Models\Order::where('notification', 1)->get()->count() +\App\Models\ReserveProduct::where('notification', 1)->get()->count()  +\App\Models\HistoryInstallment::where('notification', 1)->get()->count() > 0)
                                <span class="badge badge-pill">{{ \App\Models\Order::where('notification', 1)->get()->count() +\App\Models\ReserveProduct::where('notification', 1)->get()->count()  +\App\Models\HistoryInstallment::where('notification', 1)->get()->count()+\App\Models\TransactionSeller::get()->count() }}</span>
                            @endif
                            <i class="tio-notifications text-black"></i>
                        </a>

                        <div id="notificationDropdown" class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu"
                             style="max-height: 400px; overflow-y: auto;">
                            <div class="dropdown-item-text">
                                <span class="card-title h5">إشعارات</span>
                            </div>
                            <div class="dropdown-divider"></div>

                            <!-- Installments Notifications -->
                            @foreach (\App\Models\HistoryInstallment::where('notification', 1)->orderBy('created_at', 'desc')->get() as $installment)
                                <a href="{{ route('admin.admin.notifications.show', ['id' => $installment->id, 'type' => 'installment']) }}">
                                    <div class="media align-items-center">
                                        <i class="tio-check-circle text-success mr-2"></i>
                                        <div class="media-body">
                                            <span class="text-truncate pr-2">تحصيلات: {{ $installment->amount }}</span>
                                            <small class="text-muted">{{ $installment->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                </a>
                            @endforeach

                            <!-- Orders Notifications -->
                            @foreach (\App\Models\Order::where('notification', 1)->orderBy('created_at', 'desc')->get() as $order)
                                <a href="{{ route('admin.admin.notifications.show', ['id' => $order->id, 'type' => 'order']) }}">
                                    <div class="media align-items-center">
                                        <i class="tio-shopping-cart text-primary mr-2"></i>
                                        <div class="media-body">
                                            <span class="text-truncate pr-2">فاتورة: {{ $order->id }}</span>
                                            <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                </a>
                            @endforeach

                            <!-- Reservations Notifications -->
                            @foreach (\App\Models\ReserveProduct::where('notification', 1)->orderBy('created_at', 'desc')->get() as $reservation)
                                <a href="{{ route('admin.admin.notifications.show', ['id' => $reservation->id, 'type' => 'reserveProduct']) }}">
                                    <div class="media align-items-center">
                                        <i class="tio-calendar text-info mr-2"></i>
                                        <div class="media-body">
                                            <span class="text-truncate pr-2">حجز منتجات: {{ $reservation->id }}</span>
                                            <small class="text-muted">{{ $reservation->created_at->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                </a>
                            @endforeach

                            <!-- View All Notifications -->
                            <a class="dropdown-item text-center" href="{{ route('admin.admin.notifications.listItems') }}">
                                رؤية جميع الأشعارات
                            </a>
                        </div>
                    </div>
                </li>

                <!-- User Account -->
                <li class="nav-item">
                    <div class="hs-unfold">
                        <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper" href="javascript:;"
                           data-hs-unfold-options='{
                                 "target": "#accountNavbarDropdown",
                                 "type": "css-animation"
                               }'>
                            <div class="avatar avatar-sm avatar-circle">
                                <img class="avatar-img"
                                     onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
                                     src="{{ asset('storage/app/public/admin') }}/{{ auth('admin')->user()->image }}"
                                     alt="{{ \App\CPU\translate('image_description') }}">
                                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                            </div>
                        </a>

                        <div id="accountNavbarDropdown"
                             class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account">
                            <div class="dropdown-item-text">
                                <div class="media align-items-center">
                                    <div class="avatar avatar-sm avatar-circle mr-2">
                                        <img class="avatar-img"
                                             onerror="this.src='{{ asset('public/assets/admin/img/160x160/img1.jpg') }}'"
                                             src="{{ asset('storage/app/public/admin') }}/{{ auth('admin')->user()->image }}"
                                             alt="{{ \App\CPU\translate('image_description') }}">
                                    </div>
                                    <div class="media-body">
                                        <span class="card-title h5">{{ auth('admin')->user()->f_name }}</span>
                                        <span class="card-text">{{ auth('admin')->user()->email }}</span>
                                    </div>
                                </div>
                            </div>

                            <!--<div class="dropdown-divider"></div>-->

                            <!--<a class="dropdown-item" href="{{ route('admin.settings') }}">-->
                            <!--    <span class="text-truncate pr-2" title="{{ \App\CPU\translate('settings') }}">{{ \App\CPU\translate('بيانات المستخدم') }}</span>-->
                            <!--</a>-->

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="javascript:" onclick="Swal.fire({
                                title: 'Do you want to logout?',
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonColor: '#161853',
                                cancelButtonColor: '#363636',
                                confirmButtonText: `Yes`,
                                denyButtonText: `Don't Logout`,
                                }).then((result) => {
                                if (result.value) {
                                location.href='{{ route('admin.auth.logout') }}';
                                } else {
                                Swal.fire('Canceled', '', 'info')
                                }
                                })">
                                <span class="text-truncate pr-2" title="Sign out">{{ \App\CPU\translate('تسجيل خروج') }}</span>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <!-- End Secondary Content -->
    </div>
</header></div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('transactionSearch');
    const suggestions = document.getElementById('suggestions');

    const options = [
        { type: 4, name: "إنشاء مبيعات", url: "{{ route('admin.pos.index', ['type' => 4]) }}" },
        { type: 7, name: "إنشاء مشتريات", url: "{{ route('admin.pos.index', ['type' => 7]) }}" },
        { type: 12, name: "مردود مشتريات", url: "{{ route('admin.pos.index', ['type' => 12]) }}" },
        { type: 24, name: "مردود مبيعات", url: "{{ route('admin.pos.index', ['type' => 24]) }}" }
    ];

    searchInput.addEventListener('input', function () {
        const query = searchInput.value.trim().toLowerCase();
        suggestions.innerHTML = ''; 

        if (query) {
            const filteredOptions = options.filter(option => option.name.toLowerCase().includes(query));

            if (filteredOptions.length) {
                filteredOptions.forEach(option => {
                    const item = document.createElement('a');
                    item.classList.add('dropdown-item');
                    item.href = option.url;
                    item.textContent = option.name;
                    suggestions.appendChild(item);
                });

                suggestions.style.display = 'block';
            } else {
                suggestions.style.display = 'none';
            }
        } else {
            suggestions.style.display = 'none';
        }
    });

    document.addEventListener('click', function (event) {
        if (!searchInput.contains(event.target) && !suggestions.contains(event.target)) {
            suggestions.style.display = 'none';
        }
    });
});
</script>