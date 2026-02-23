@extends('layouts.admin.app')

@section('title', \App\CPU\translate('إعدادات ZATCA'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ asset('public/assets/admin') }}/css/custom.css" />
    <style>
        .zatca-card {
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .zatca-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .zatca-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
        }
        .status-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active {
            background-color: #10b981;
            color: white;
        }
        .status-pending {
            background-color: #f59e0b;
            color: white;
        }
        .egs-unit-card {
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }
        .egs-unit-card:hover {
            border-left-color: #764ba2;
            transform: translateX(5px);
        }
        .icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 24px;
        }
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }
        .job-status {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            display: none;
        }
        .job-status.processing {
            background-color: #e3f2fd;
            color: #1976d2;
        }
        .job-status.completed {
            background-color: #e8f5e9;
            color: #388e3c;
        }
        .job-status.failed {
            background-color: #ffebee;
            color: #d32f2f;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard') }}" class="text-secondary">
                            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="#" class="text-primary">
                            {{ \App\CPU\translate('إعدادات ZATCA') }}
                        </a>
                    </li>
                </ol>
            </nav>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card zatca-card">
                    <div class="zatca-header">
                        <div class="d-flex align-items-center">
                            <div class="icon-circle me-3">
                                <i class="tio-settings-outlined"></i>
                            </div>
                            <div>
                                <h4 class="mb-0">{{ \App\CPU\translate('إعدادات ZATCA') }}</h4>
                                <p class="mb-0 opacity-75">{{ \App\CPU\translate('إعدادات الفواتير الإلكترونية') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <ul class="nav nav-tabs mb-4" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="company-tab" data-toggle="tab" href="#company" role="tab">
                                    <i class="tio-building"></i> {{ \App\CPU\translate('معلومات الشركة') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="egs-units-tab" data-toggle="tab" href="#egs-units" role="tab">
                                    <i class="tio-devices"></i> {{ \App\CPU\translate('وحدات EGS') }}
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Company Settings Tab -->
                            <div class="tab-pane fade show active" id="company" role="tabpanel">
                                <form action="{{ route('admin.zatca-settings.store-company') }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('الرقم الضريبي / TIN') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="vat_tin" class="form-control" 
                                                   value="{{ $companySettings->vat_tin ?? '' }}" 
                                                   placeholder="123456789012345" required maxlength="15">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('رقم السجل التجاري') }}</label>
                                            <input type="text" name="cr_number" class="form-control" 
                                                   value="{{ $companySettings->cr_number ?? '' }}" 
                                                   placeholder="CR123456789">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('اسم الشركة (عربي)') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name_ar" class="form-control" 
                                                   value="{{ $companySettings->company_name_ar ?? '' }}" 
                                                   placeholder="اسم الشركة بالعربية" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('اسم الشركة (إنجليزي)') }} <span class="text-danger">*</span></label>
                                            <input type="text" name="company_name_en" class="form-control" 
                                                   value="{{ $companySettings->company_name_en ?? '' }}" 
                                                   placeholder="Company Name in English" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('العنوان (عربي)') }}</label>
                                            <textarea name="address_ar" class="form-control" rows="3" 
                                                      placeholder="العنوان الكامل بالعربية">{{ $companySettings->address_ar ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('العنوان (إنجليزي)') }}</label>
                                            <textarea name="address_en" class="form-control" rows="3" 
                                                      placeholder="Full Address in English">{{ $companySettings->address_en ?? '' }}</textarea>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">{{ \App\CPU\translate('البيئة') }} <span class="text-danger">*</span></label>
                                            <select name="environment" class="form-control" required>
                                                <option value="simulation" {{ ($companySettings->environment ?? 'simulation') == 'simulation' ? 'selected' : '' }}>
                                                    {{ \App\CPU\translate('تجريبي (Simulation)') }}
                                                </option>
                                                <option value="production" {{ ($companySettings->environment ?? '') == 'production' ? 'selected' : '' }}>
                                                    {{ \App\CPU\translate('إنتاجي (Production)') }}
                                                </option>
                                            </select>
                                            <small class="form-text text-muted">
                                                {{ \App\CPU\translate('استخدم التجريبي للاختبار والإنتاجي للاستخدام الفعلي') }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn btn-primary px-4 py-2">
                                            <i class="tio-save"></i> {{ \App\CPU\translate('حفظ') }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- EGS Units Tab -->
                            <div class="tab-pane fade" id="egs-units" role="tabpanel">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="mb-0">{{ \App\CPU\translate('وحدات EGS') }}</h5>
                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#egsUnitModal">
                                        <i class="tio-add"></i> {{ \App\CPU\translate('إضافة وحدة جديدة') }}
                                    </button>
                                </div>

                                @if($egsUnits->count() > 0)
                                    <div class="row">
                                        @foreach($egsUnits as $egsUnit)
                                            <div class="col-md-6 mb-3">
                                                <div class="card egs-unit-card">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div>
                                                                <h6 class="mb-1">{{ $egsUnit->name }}</h6>
                                                                <p class="text-muted mb-0 small">{{ $egsUnit->egs_id }}</p>
                                                            </div>
                                                            <span class="status-badge {{ $egsUnit->status == 'active' ? 'status-active' : 'status-pending' }}">
                                                                {{ $egsUnit->status == 'active' ? \App\CPU\translate('نشط') : \App\CPU\translate('قيد الانتظار') }}
                                                            </span>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted">
                                                                <i class="tio-tag"></i> {{ \App\CPU\translate('النوع') }}: 
                                                                <strong>{{ $egsUnit->type == 'branch' ? \App\CPU\translate('فرع') : \App\CPU\translate('كاشير') }}</strong>
                                                            </small>
                                                        </div>
                                                        @if($egsUnit->branch)
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="tio-building"></i> {{ \App\CPU\translate('الفرع') }}: 
                                                                    <strong>{{ $egsUnit->branch->name ?? '' }}</strong>
                                                                </small>
                                                            </div>
                                                        @endif
                                                        @if($egsUnit->onboarded_at)
                                                            <div class="mb-2">
                                                                <small class="text-muted">
                                                                    <i class="tio-time"></i> {{ \App\CPU\translate('تاريخ التفعيل') }}: 
                                                                    <strong>{{ $egsUnit->onboarded_at->format('Y-m-d H:i') }}</strong>
                                                                </small>
                                                            </div>
                                                        @endif
                                                        <div class="action-buttons" id="actions-{{ $egsUnit->id }}">
                                                            <button type="button" class="btn btn-sm btn-primary" 
                                                                    onclick="generateCsr({{ $egsUnit->id }})"
                                                                    {{ $egsUnit->csr_path ? 'disabled' : '' }}>
                                                                <i class="tio-key"></i> {{ \App\CPU\translate('إنشاء CSR') }}
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="openOnboardModal({{ $egsUnit->id }})"
                                                                    {{ !$egsUnit->csr_path ? 'disabled' : '' }}>
                                                                <i class="tio-checkmark-circle"></i> {{ \App\CPU\translate('تفعيل') }}
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-warning" 
                                                                    onclick="openTestModal({{ $egsUnit->id }})"
                                                                    {{ !$egsUnit->isOnboarded() ? 'disabled' : '' }}>
                                                                <i class="tio-play"></i> {{ \App\CPU\translate('اختبار') }}
                                                            </button>
                                                        </div>
                                                        <div class="job-status" id="job-status-{{ $egsUnit->id }}"></div>
                                                        <div class="d-flex justify-content-end mt-3">
                                                            <button type="button" class="btn btn-sm btn-info me-2" 
                                                                    onclick="editEgsUnit({{ json_encode($egsUnit) }})">
                                                                <i class="tio-edit"></i> {{ \App\CPU\translate('تعديل') }}
                                                            </button>
                                                            <form action="{{ route('admin.zatca-settings.delete-egs-unit', $egsUnit->id) }}" 
                                                                  method="POST" class="d-inline" 
                                                                  onsubmit="return confirm('{{ \App\CPU\translate('هل أنت متأكد من الحذف؟') }}')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="tio-delete"></i> {{ \App\CPU\translate('حذف') }}
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5">
                                        <i class="tio-inbox" style="font-size: 64px; color: #ccc;"></i>
                                        <p class="text-muted mt-3">{{ \App\CPU\translate('لا توجد وحدات EGS') }}</p>
                                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#egsUnitModal">
                                            <i class="tio-add"></i> {{ \App\CPU\translate('إضافة وحدة جديدة') }}
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Onboarding Modal -->
    <div class="modal fade" id="onboardModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('تفعيل وحدة EGS') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="onboardForm">
                    @csrf
                    <input type="hidden" id="onboard_egs_unit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('OTP من بوابة ZATCA') }} <span class="text-danger">*</span></label>
                            <input type="text" id="onboard_otp" class="form-control" 
                                   placeholder="{{ \App\CPU\translate('أدخل OTP') }}" required maxlength="10">
                            <small class="form-text text-muted">
                                {{ \App\CPU\translate('احصل على OTP من بوابة Fatoora. لن يتم حفظ OTP.') }}
                            </small>
                        </div>
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('البيئة') }}</label>
                            <select id="onboard_environment" class="form-control">
                                <option value="simulation">{{ \App\CPU\translate('تجريبي (Simulation)') }}</option>
                                <option value="production">{{ \App\CPU\translate('إنتاجي (Production)') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('إلغاء') }}</button>
                        <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('تفعيل') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Test Submission Modal -->
    <div class="modal fade" id="testModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ \App\CPU\translate('اختبار إرسال الفاتورة') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="testForm">
                    @csrf
                    <input type="hidden" id="test_egs_unit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('رقم الطلب') }} <span class="text-danger">*</span></label>
                            <input type="number" id="test_order_id" class="form-control" 
                                   placeholder="{{ \App\CPU\translate('أدخل رقم الطلب') }}" required>
                            <small class="form-text text-muted">
                                {{ \App\CPU\translate('أدخل رقم الطلب المراد اختباره') }}
                            </small>
                        </div>
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('البيئة') }}</label>
                            <select id="test_environment" class="form-control">
                                <option value="simulation">{{ \App\CPU\translate('تجريبي (Simulation)') }}</option>
                                <option value="production">{{ \App\CPU\translate('إنتاجي (Production)') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('إلغاء') }}</button>
                        <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('اختبار') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- EGS Unit Modal -->
    <div class="modal fade" id="egsUnitModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="egsUnitModalTitle">{{ \App\CPU\translate('إضافة وحدة EGS جديدة') }}</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.zatca-settings.store-egs-unit') }}" method="POST" id="egsUnitForm">
                    @csrf
                    <input type="hidden" name="id" id="egs_unit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('معرف EGS') }} <span class="text-danger">*</span></label>
                            <input type="text" name="egs_id" id="egs_id" class="form-control" 
                                   placeholder="EGS_01" required maxlength="50">
                            <small class="form-text text-muted">{{ \App\CPU\translate('مثال: EGS_01, EGS_02') }}</small>
                        </div>
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('الاسم') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="egs_name" class="form-control" 
                                   placeholder="{{ \App\CPU\translate('اسم الوحدة') }}" required>
                        </div>
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('النوع') }} <span class="text-danger">*</span></label>
                            <select name="type" id="egs_type" class="form-control" required>
                                <option value="branch">{{ \App\CPU\translate('فرع') }}</option>
                                <option value="cashier">{{ \App\CPU\translate('كاشير') }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>{{ \App\CPU\translate('الفرع') }}</label>
                            <select name="branch_id" id="egs_branch_id" class="form-control">
                                <option value="">{{ \App\CPU\translate('اختر الفرع') }}</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ \App\CPU\translate('إلغاء') }}</button>
                        <button type="submit" class="btn btn-primary">{{ \App\CPU\translate('حفظ') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    function editEgsUnit(egsUnit) {
        document.getElementById('egsUnitModalTitle').textContent = '{{ \App\CPU\translate('تعديل وحدة EGS') }}';
        document.getElementById('egs_unit_id').value = egsUnit.id;
        document.getElementById('egs_id').value = egsUnit.egs_id;
        document.getElementById('egs_name').value = egsUnit.name;
        document.getElementById('egs_type').value = egsUnit.type;
        document.getElementById('egs_branch_id').value = egsUnit.branch_id || '';
        $('#egsUnitModal').modal('show');
    }

    $('#egsUnitModal').on('hidden.bs.modal', function () {
        document.getElementById('egsUnitForm').reset();
        document.getElementById('egs_unit_id').value = '';
        document.getElementById('egsUnitModalTitle').textContent = '{{ \App\CPU\translate('إضافة وحدة EGS جديدة') }}';
    });

    function generateCsr(egsUnitId) {
        if (!confirm('{{ \App\CPU\translate('هل تريد إنشاء CSR والمفاتيح لهذه الوحدة؟') }}')) {
            return;
        }

        const statusDiv = document.getElementById('job-status-' + egsUnitId);
        statusDiv.className = 'job-status processing';
        statusDiv.style.display = 'block';
        statusDiv.innerHTML = '<i class="tio-sync"></i> {{ \App\CPU\translate('جاري إنشاء CSR...') }}';

        fetch('{{ route("admin.zatca-settings.generate-csr", ":id") }}'.replace(':id', egsUnitId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pollJobStatus(data.job_id, egsUnitId);
            } else {
                statusDiv.className = 'job-status failed';
                statusDiv.innerHTML = '<i class="tio-error"></i> ' + data.message;
            }
        })
        .catch(error => {
            statusDiv.className = 'job-status failed';
            statusDiv.innerHTML = '<i class="tio-error"></i> {{ \App\CPU\translate('حدث خطأ') }}: ' + error.message;
        });
    }

    function openOnboardModal(egsUnitId) {
        document.getElementById('onboard_egs_unit_id').value = egsUnitId;
        document.getElementById('onboard_otp').value = '';
        $('#onboardModal').modal('show');
    }

    function openTestModal(egsUnitId) {
        document.getElementById('test_egs_unit_id').value = egsUnitId;
        document.getElementById('test_order_id').value = '';
        $('#testModal').modal('show');
    }

    document.getElementById('onboardForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const egsUnitId = document.getElementById('onboard_egs_unit_id').value;
        const otp = document.getElementById('onboard_otp').value;
        const environment = document.getElementById('onboard_environment').value;

        const statusDiv = document.getElementById('job-status-' + egsUnitId);
        statusDiv.className = 'job-status processing';
        statusDiv.style.display = 'block';
        statusDiv.innerHTML = '<i class="tio-sync"></i> {{ \App\CPU\translate('جاري التفعيل...') }}';

        $('#onboardModal').modal('hide');

        fetch('{{ route("admin.zatca-settings.onboard", ":id") }}'.replace(':id', egsUnitId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                otp: otp,
                environment: environment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pollJobStatus(data.job_id, egsUnitId);
            } else {
                statusDiv.className = 'job-status failed';
                statusDiv.innerHTML = '<i class="tio-error"></i> ' + data.message;
            }
        })
        .catch(error => {
            statusDiv.className = 'job-status failed';
            statusDiv.innerHTML = '<i class="tio-error"></i> {{ \App\CPU\translate('حدث خطأ') }}: ' + error.message;
        });
    });

    document.getElementById('testForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const egsUnitId = document.getElementById('test_egs_unit_id').value;
        const orderId = document.getElementById('test_order_id').value;
        const environment = document.getElementById('test_environment').value;

        const statusDiv = document.getElementById('job-status-' + egsUnitId);
        statusDiv.className = 'job-status processing';
        statusDiv.style.display = 'block';
        statusDiv.innerHTML = '<i class="tio-sync"></i> {{ \App\CPU\translate('جاري الاختبار...') }}';

        $('#testModal').modal('hide');

        fetch('{{ route("admin.zatca-settings.test-submission", ":id") }}'.replace(':id', egsUnitId), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                order_id: orderId,
                environment: environment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                pollJobStatus(data.job_id, egsUnitId);
            } else {
                statusDiv.className = 'job-status failed';
                statusDiv.innerHTML = '<i class="tio-error"></i> ' + data.message;
            }
        })
        .catch(error => {
            statusDiv.className = 'job-status failed';
            statusDiv.innerHTML = '<i class="tio-error"></i> {{ \App\CPU\translate('حدث خطأ') }}: ' + error.message;
        });
    });

    function pollJobStatus(jobId, egsUnitId) {
        const statusDiv = document.getElementById('job-status-' + egsUnitId);
        const maxAttempts = 60; // 5 minutes max
        let attempts = 0;

        const poll = setInterval(function() {
            attempts++;
            
            fetch('{{ route("admin.zatca-settings.job-status", ":jobId") }}'.replace(':jobId', jobId))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        if (data.status === 'completed') {
                            clearInterval(poll);
                            statusDiv.className = 'job-status completed';
                            statusDiv.innerHTML = '<i class="tio-checkmark-circle"></i> ' + data.message;
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else if (data.status === 'failed') {
                            clearInterval(poll);
                            statusDiv.className = 'job-status failed';
                            statusDiv.innerHTML = '<i class="tio-error"></i> ' + data.message;
                        } else {
                            statusDiv.innerHTML = '<i class="tio-sync"></i> ' + data.message;
                        }
                    }

                    if (attempts >= maxAttempts) {
                        clearInterval(poll);
                        statusDiv.className = 'job-status failed';
                        statusDiv.innerHTML = '<i class="tio-error"></i> {{ \App\CPU\translate('انتهت مهلة الانتظار') }}';
                    }
                })
                .catch(error => {
                    if (attempts >= maxAttempts) {
                        clearInterval(poll);
                        statusDiv.className = 'job-status failed';
                        statusDiv.innerHTML = '<i class="tio-error"></i> {{ \App\CPU\translate('حدث خطأ في الاتصال') }}';
                    }
                });
        }, 5000); // Poll every 5 seconds
    }
</script>
@endpush
