<div id="{{ $id }}" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="text-dark">{{ $title }}</h4>
        <h4 class="text-dark">{{ $total }} ريال</h4>
    </div>

    <div class="section-body">
        @include($partial)
    </div>
</div>
