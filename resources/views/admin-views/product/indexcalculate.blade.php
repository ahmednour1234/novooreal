<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>حاسبة السعر مع الضريبة والخصم</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f9;
            color: #333;
            direction: rtl;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-top: 50px;
        }
        h1 {
            text-align: center;
            color: #4CAF50;
        }
        label {
            font-size: 1.1em;
            margin-bottom: 5px;
            display: block;
        }
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            font-size: 1.1em;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .result {
            margin-top: 30px;
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .result p {
            font-size: 1.2em;
        }
        .error {
            color: red;
            font-size: 1.2em;
            text-align: center;
            margin-top: 20px;
        }
        .final-price {
            font-size: 1.5em;
            font-weight: bold;
            color: #4CAF50;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>حاسبة السعر مع الضريبة والخصم</h1>

<form method="POST" action="{{ route('indexcalculate') }}">
    @csrf
    <label for="price">السعر الأساسي:</label>
    <input type="number" name="price" id="price" step="0.01" value="{{ old('price', $price) }}" required>

    <label for="tax_rate">نسبة الضريبة (%):</label>
    <input type="number" name="tax_rate" id="tax_rate" step="0.01" value="{{ old('tax_rate', $tax_rate) }}" required>

    <label for="discount_rate">نسبة الخصم (%):</label>
    <input type="number" name="discount_rate" id="discount_rate" step="0.01" value="{{ old('discount_rate', $discount_rate) }}" required>

    <button type="submit">احسب</button>
</form>

    @if($error_message)
        <div class="error">{{ $error_message }}</div>
    @endif

    @if($final_price > 0)
        <div class="result">
            <p>السعر الأساسي: <strong>{{ number_format($price, 2) }} ج.م</strong></p>
            <p>الضريبة: <strong>{{ number_format($tax, 2) }} ج.م</strong></p>
            <p>الخصم: <strong>{{ number_format($discount, 2) }} ج.م</strong></p>
            <p class="final-price">السعر النهائي: <strong>{{ number_format($final_price, 2) }} ج.م</strong></p>
        </div>
    @endif
</div>

</body>
</html>
