<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Invoice</title>
    @vite('resources/js/billing-invoice.js')
</head>
<body>
    <div id="billing-invoice-app" data-uuid="{{ $uuid }}"></div>
</body>
</html>
