<!-- حقوق الملكية -->

<table class="table">
    <thead>
        <tr>
<th colspan="2">
    حقوق ملكية
</th>   
</tr>
    </thead>
    <tbody>
        @foreach($equityAccounts as $eq)
            @php renderAccountRow($eq); @endphp
        @endforeach
    </tbody>
</table>

<!-- الالتزامات غير المتداولة -->
<table class="table">
    <thead>
        <tr>
  <th colspan="2">
الالتزامات غير المتداولة
</th>   
        </tr>
    </thead>
    <tbody>
        @php renderAccountRow($nonCurrentLiabilities); @endphp
    </tbody>
</table>
