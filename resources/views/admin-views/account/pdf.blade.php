<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\CPU\translate('أرصدة الحسابات') }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ \App\CPU\translate('أرصدة الحسابات') }}</h1>
    <table>
        <thead>
            <tr>
                <th>{{ \App\CPU\translate('معلومات الحساب') }}</th>
                <th>{{ \App\CPU\translate('معلومات الميزانية') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($accounts as $key => $account)
            <tr>
                <td>
                    <strong>{{ $account->account }}</strong><br>
                    <span>{{ $account->account_number }}</span><br>
                    <span>{{ $account->description }}</span>
                </td>
                <td>
                    <span>{{ \App\CPU\translate('الاجمالي') }}: <strong>{{ $account->balance }}</strong></span><br>
                    <span>{{ \App\CPU\translate('الإيرادات الحساب') }}: <strong>{{ $account->total_in ?? 0 }}</strong></span><br>
                    <span>{{ \App\CPU\translate(' مصروفات الحساب') }}: <strong>{{ $account->total_out ?? 0 }}</strong></span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
  <p class="text-center">
        {{ \App\CPU\translate('تم إنشاء هذا التقرير بواسطة') }} {{ $seller->email }}
    </p>
    </body>
</html>
