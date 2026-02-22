@extends('layouts.admin.app')

@section('title',\App\CPU\translate('add_new_unit_type'))

@push('css_or_js')

@endpush

@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->

  {{-- ====== Breadcrumb ====== --}}
  <div class="mb-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-white px-3 py-2 rounded shadow-sm">
        <li class="breadcrumb-item">
          <a href="{{ route('admin.dashboard') }}" class="text-secondary">
            <i class="tio-home-outlined"></i> {{ \App\CPU\translate('الرئيسية') }}
          </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
{{\App\CPU\translate('اضافة وحدات القياس')}}        </li>
        
      </ol>
    </nav>
  </div>
    <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
<form action="{{ route('admin.unit.store', ['units' => $type]) }}" method="post">
    @csrf
    <div class="row mb-4">
        <!-- Unit Type Input -->
        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label for="unit_type" class="font-weight-bold">{{ \App\CPU\translate('الوحدة') }}</label>
                <input type="text" id="unit_type" name="unit_type" value="{{ old('unit_type') }}" class="form-control border-primary" placeholder="{{ \App\CPU\translate('unit') }}">
            </div>
        </div>

        <!-- Conditional Unit Type Dropdown (If $type is 1) -->
        @if($type == 1)
        <div class="col-12 col-sm-6">
            <div class="form-group">
                <label for="unit_id" class="font-weight-bold">{{ \App\CPU\translate('الوحدة') }}</label>
                <select name="unit_id" id="unit_id" class="form-control border-primary">
                    <option value="">{{ \App\CPU\translate('اختر الوحدة') }}</option>
                    @foreach($unitall as $unit)
                        <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->unit_type }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif
    </div>

    <!-- Submit Button (left-aligned) -->
    <div class="form-group mb-0">
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-left-fallback col-3">
                {{ \App\CPU\translate('حفظ') }}
            </button>
        </div>
    </div>
</form>
                    </div>
                </div>
            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <div>
                                                     @if($type == 2)
                            <h5>{{ \App\CPU\translate('جدول وحدات القياس الكبري')}} <span class="badge badge-soft-dark">{{$unitss->total()}}</span></h5>
                            @else
                          <h5>{{ \App\CPU\translate('جدول وحدات القياس الصغري')}} <span class="badge badge-soft-dark">{{$unitss->total()}}</span></h5>
                         @endif
                        </div>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive ">
                        <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                            <tr>
                                <th>{{\App\CPU\translate('#')}}</th>
                                <th>{{\App\CPU\translate('الوحدة')}}</th>
                                <th>{{\App\CPU\translate('إجراءات')}}</th>
                            </tr>

                            </thead>

                            <tbody>
                            @foreach ($unitss as $key=>$unit)
                                <tr>
                                    <td>{{ $unitss->firstItem()+$key }}</td>
                                    <td>
                                    @if($type == 2)
                                        {{ $unit->unit_type }}
                                        @else
                                        {{ $unit->name }}
                                        @endif
                                    </td>
                                    <td>
                                        <a class="btn btn-white mr-1"
href="{{ route('admin.unit.edit', ['id' => $unit['id'], 'type' => $type]) }}">
    <span class="tio-edit"></span>
</a>
<!--                                        <a class="btn btn-white mr-1" href="javascript:"-->
<!--                                                   onclick="form_alert('unit-{{$unit['id']}}','Want to delete this Unit Type?')"><span class="tio-delete"></span></a>-->
<!--<form action="{{ route('admin.unit.delete', ['id' => $unit['id'], 'units' => $type]) }}" method="post" id="unit-{{$unit['id']}}">-->
<!--                                                    @csrf @method('delete')-->
<!--                                                </form>-->
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <hr>
                        <table>
                            <tfoot>
                            {!! $unitss->links() !!}
                            </tfoot>
                        </table>
                        @if(count($unitss)==0)
                            <div class="text-center p-4">
                                <img class="mb-3 img-one-un" src="{{asset('public/assets/admin')}}/svg/illustrations/sorry.svg" alt="{{\App\CPU\translate('image_description')}}">
                                <p class="mb-0">{{ \App\CPU\translate('لاتوجد بيانات لعرضها')}}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
</div>
@endsection
