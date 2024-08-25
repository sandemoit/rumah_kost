@extends('layouts.app')
@push('load-css')
    <link rel="stylesheet" href="{{ asset('assets/css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
@endpush
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')

        <div class="app-content">
            <div class="container-fluid justify-content-center align-items-center">
                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-10 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row justify-content-center">
                                    <!-- Pilihan Buku Kas (kiri) -->
                                    <div class="col reportSelect text-start mt-3">
                                        <div class="form-group" id="pilihBukuKas">
                                            <label for="selectReport" class="labelSelect">Pilih Buku Kas</label>
                                            <select class="form-select" id="selectReportActivity">
                                                <option value="all">Semua Buku Kas</option>
                                                @foreach ($kontrakan as $item)
                                                    <option value="{{ $item->code_kontrakan }}">{{ $item->nama_kontrakan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Pilihan Tahun (kanan) -->
                                    <div class="col text-center">
                                        <div class="bulannav">
                                            <a href="javascript:void(0)" class="bulan_nav_left" id="bulan_nav_left"
                                                title="Bulan sebelumnya">&nbsp;</a>
                                            <a href="javascript:void(0)" class="bulan_nav_right" id="bulan_nav_right"
                                                title="Bulan selanjutnya">&nbsp;</a>
                                            <div class="bulankas" id="bulankasreport"></div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tombol Download (kanan) -->
                                <div class="row justify-content-center text-center mt-3">
                                    <div class="col mt-3">
                                        <a id="export" title="Export to Excel" class="btn btn-success">
                                            <i class="bi bi-file-earmark-arrow-down"></i> Download Excel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::App Content-->
    </main>
@endsection
@push('custom-js')
    {{-- definie config --}}

    <input type="hidden" id="endpoint" value="{{ env('APP_URL') }}">
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/laporan/bulanan/ringkasan.js') }}"></script>
@endpush
