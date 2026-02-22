<!-- الإيرادات التشغيلية -->
<table class="table">
    <thead>
        <tr>
          <th colspan="2">
الإيرادات التشغيلية
</th>   
        </tr>
    </thead>
    <tbody>
        @foreach($operatingRevenues as $rev)
            @php renderAccountRow($rev); @endphp
        @endforeach
        <tr>
            <td colspan="2" class="text-right"><strong>إجمالي الإيرادات التشغيلية: {{ number_format($totalRevenues) }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- الموجودات المتداولة -->
<table class="table">
    <thead>
        <tr>
              <th colspan="2">
أصول المتداولة
</th>   
        </tr>
    </thead>
    <tbody>
        @php renderAccountRow($currentAssets); @endphp
        <tr>
            <td colspan="2" class="text-right"><strong>إجمالي أصول المتداولة: {{ number_format($currentAssets->aggregated_balance) }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- الالتزامات المتداولة -->
<table class="table">
    <thead>
        <tr>
              <th colspan="2">
الالتزامات المتداولة
</th>   
        </tr>
    </thead>
    <tbody>
        @php renderAccountRow($currentLiabilities); @endphp
        <tr>
            <td colspan="2" class="text-right"><strong>إجمالي الالتزامات المتداولة: {{ number_format($currentLiabilities->aggregated_balance) }}</strong></td>
        </tr>
    </tbody>
</table>

<!-- المصروفات التشغيلية -->
<table class="table">
    <thead>
        <tr>
                      <th colspan="2">
المصروفات التشغيلية
</th>   
        </tr>
    </thead>
    <tbody>
        @php renderAccountRow($operatingExpenses); @endphp
        <tr>
            <td colspan="2" class="text-right"><strong>إجمالي المصروفات التشغيلية: {{ number_format($operatingExpenses->aggregated_balance) }}</strong></td>
        </tr>
    </tbody>
</table>
