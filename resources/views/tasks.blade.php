<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Task Tracker</title>
    @vite(['resources/css/app.css'])
</head>
<body>
<div id="root"></div>

@vite(['resources/js/app.tsx'])
</body>
</html>
