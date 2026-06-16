<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Token</title>
    @vite('resources/js/billing-token.js')
</head>
<body>
    <div
        id="billing-token-app"
        data-uuid="{{ $uuid }}"
        data-custinfo="{{ request()->boolean('custinfo') ? '1' : '0' }}"
    ></div>
</body>
</html>
