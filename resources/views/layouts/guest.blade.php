<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="_token" content="{!! csrf_token() !!}" />
    <title>{{ config('app.name') }}</title>

    <!-- Favicons -->
    <link rel="shortcut icon" href="{{ Storage::url('favicon/favicon.ico') }}">
    <link rel="icon" href="{{ Storage::url('favicon/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" href="{{ Storage::url('favicon/favicon-192x1922.png') }}" sizes="192x192" />
    <link rel="apple-touch-icon" href="{{ Storage::url('favicon/apple-touch-icon.png') }}" />

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css"
        integrity="sha256-Qsx5lrStHZyR9REqhUF8iQt73X06c8LGIUPzpOhwRrI=" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ url('assets') }}/css/adminlte.css">
    <link rel="stylesheet" href="{{ url('assets') }}/css/custom.css">

<body class="login-page bg-body-secondary">

    {{ $slot }}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
        integrity="sha256-YMa+wAM6QkVyz999odX7lPRxkoYAan8suedu4k2Zur8=" crossorigin="anonymous"></script>
    <script src="{{ url('assets') }}/js/adminlte.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- sweet alert --}}
    @if ($message = Session::get('success'))
        <script>
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                iconColor: 'white',
                customClass: {
                    popup: 'colored-toast',
                },
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            // Panggil Toast.fire() untuk menampilkan pesan toast
            Toast.fire({
                icon: 'success',
                title: 'Berhasil keluar',
            })
        </script>
    @endif
</body>

</html>
