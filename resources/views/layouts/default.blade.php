<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/67973cf856.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/clipboard@2.0.10/dist/clipboard.min.js"></script>
    <script> var clipboard = new ClipboardJS('.btn'); </script>
    <title>@yield('title')</title>
</head>
<body>
    @include('layouts.menu')
    <br>
    <div class="container">
        <div> @yield('content') </div>
    </div>
    @include('layouts.footer')
</body>
</html>