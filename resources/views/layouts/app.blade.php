<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="{{ session('theme', 'light') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />


    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/app.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/iconly.css') }}">
    <link rel="stylesheet" crossorigin href="{{ asset('mazer/compiled/css/custom.css') }}">

    <!-- CSS Select2 -->



    <!-- Scripts -->
    @vite(['resources/css/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app">
        <livewire:layout.sidebar />

        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block">
                    <i class="bi bi-list fs-3"></i>
                </a>
            </header>
            {{ $slot }}
        </div>
    </div>

    <!--================== SWEET ALERT ==================-->
    @push('scripts')
        <script>
            window.addEventListener("load", function() {
                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: '{{ session('success') }}',
                        timer: 2000,
                        showConfirmButton: false
                    });
                @endif

                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: '{{ session('error') }}'
                    });
                @endif
            });
            document.addEventListener('show-alert', function(event) {
                const detail = event.detail[0] || event.detail;

                Swal.fire({
                    icon: detail.type,
                    title: detail.type === 'success' ? 'Berhasil!' : 'Gagal!',
                    text: detail.message,
                    timer: detail.type === 'success' ? 2000 : null,
                    showConfirmButton: detail.type !== 'success'
                });
            });
            document.addEventListener('alpine:init', () => {
                Alpine.directive('currency', (el, {}, {
                    cleanup
                }) => {
                    const formatRupiah = (value) => {
                        if (!value) return '';
                        let number = value.toString().replace(/[^0-9]/g, '');
                        if (!number) return '';
                        return 'Rp ' + parseInt(number).toLocaleString('id-ID');
                    };

                    const parseRupiah = (value) => {
                        return value.replace(/[^0-9]/g, '');
                    };

                    let isFormatting = false;

                    const handleInput = (e) => {
                        if (isFormatting) return;

                        isFormatting = true;
                        const rawValue = parseRupiah(e.target.value);
                        e.target.value = formatRupiah(rawValue);
                        isFormatting = false;
                    };

                    // Format initial value hanya sekali
                    const initializeValue = () => {
                        if (el.value && !el.dataset.formatted) {
                            // Hanya format jika belum ada prefix "Rp"
                            if (!el.value.toString().startsWith('Rp')) {
                                el.value = formatRupiah(el.value);
                            }
                            el.dataset.formatted = 'true';
                        }
                    };

                    // Tunggu Livewire selesai render
                    if (window.Livewire) {
                        Livewire.hook('morph.updated', () => {
                            initializeValue();
                        });
                    }

                    // Untuk initial load
                    setTimeout(initializeValue, 50);

                    el.addEventListener('input', handleInput);

                    cleanup(() => {
                        el.removeEventListener('input', handleInput);
                    });
                });
            });
        </script>
    @endpush
    <!--================== END ==================-->

    <!-- script kebutuhan template -->
    <script src="{{ asset('mazer/extensions/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>

    <script src="{{ asset('mazer/compiled/js/app.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/custom.js') }}"></script>
    <script src="{{ asset('mazer/compiled/js/DataAkun-delete.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('scripts')
</body>




</html>
