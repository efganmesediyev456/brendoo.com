<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Waybill</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
    </style>
</head>
<body>
<h2>Waybill</h2>
<p><strong>Barkod:</strong> {{ $barcode }}</p>
<p><strong>Müştəri:</strong> {{ $customerName }}</p>
<p><strong>Alt kateqoriyalar:</strong></p>
<ul>
    @foreach($subCategories as $title)
        <li>{{ $title }}</li>
    @endforeach
</ul>
</body>
</html>
