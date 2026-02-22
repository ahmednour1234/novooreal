<!-- الأصول غير المتداولة -->
<table class="table">
    <thead>
        <tr>
<th colspan="2">
الأصول غير المتداولة
</th>   
        </tr>
    </thead>
    <tbody>
        @php renderAccountRow($investmentActivities); @endphp
        <tr>
            <td colspan="2" class="text-right"><strong>إجمالي الأنشطة الاستثمارية: {{ number_format($netInvestment) }}</strong></td>
        </tr>
    </tbody>
</table>
