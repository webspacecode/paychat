<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">

<style>

/* ✅ NORMAL SCREEN VIEW */
body {
    margin: 0;
    padding: 8px;
    font-family: monospace;
    color: #111;
}

/* Layout */
.header {
    text-align: center;
}

.logo {
    max-height: 50px;
    margin-bottom: 5px;
}

.company {
    font-size: 16px;
    font-weight: bold;
}

.meta {
    font-size: 11px;
    color: #555;
}

.divider {
    border-top: 1px dashed #ccc;
    margin: 6px 0;
}

.row {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
}

.total {
    font-size: 14px;
    font-weight: bold;
}

.footer {
    text-align: center;
    font-size: 11px;
    margin-top: 10px;
}

/* ✅ PRINT SETTINGS (VERY IMPORTANT) */
@media print {
    body {
        width: 80mm;   /* 👈 change dynamically later if needed */
        margin: 0;
        padding: 6px;
    }

    @page {
        size: 80mm auto;
        margin: 0;
    }

    * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}

</style>

</head>

<body>

@yield('content')

</body>
</html>