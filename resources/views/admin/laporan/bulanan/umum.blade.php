@push('load-css')
    <link rel="stylesheet" href="{{ asset('assets/css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 id="headingTitle" class="mb-0">Semua Buku Kas</h3>
                        <p>{{ __($pageTitle) }}</p>
                    </div>
                    @stack('custom-button')
                </div>
            </div>
        </div>
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row">
                    <div class="col-md-6 reportSelect">
                        <div class="form-group" id="pilihBukuKas">
                            <label for="selectReport" class="labelSelect">Pilih Buku Kas</label>
                            <select class="form-select" id="selectReport">
                                <option value="all">Semua Buku Kas</option>
                                @foreach ($kontrakan as $item)
                                    <option value="{{ $item->code_kontrakan }}">{{ $item->nama_kontrakan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="bulannav">
                            <a href="javascript:void(0)" class="bulan_nav_left" id="bulan_nav_left"
                                title="Bulan sebelumnya">&nbsp;</a>
                            <a href="javascript:void(0)" class="bulan_nav_right" id="bulan_nav_right"
                                title="Bulan selanjutnya">&nbsp;</a>
                            <div class="bulankas" id="bulankasreport"></div>
                        </div>
                    </div>
                </div>

                <!-- Report Section -->
                <div class="report-section">
                    <div class="card">
                        <div class="card-header" id="cardTitle">
                            <i class="bi bi-journal-text"></i> Semua Buku Kas
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a href="{{ route('laporan.bulanan.umum') }}"
                                        class="nav-link {{ request()->segment(2) == 'bulanan' && !request()->segment(3) ? 'active' : (request()->segment(3) == 'umum' ? 'active' : '') }}"
                                        role="tab">Umum</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a href="{{ route('laporan.bulanan.aktivitas') }}"
                                        class="nav-link {{ request()->segment(3) == 'aktivitas' ? 'active' : '' }}"
                                        role="tab">Aktivitas</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a href="{{ route('laporan.bulanan.ringkasan') }}"
                                        class="nav-link {{ request()->segment(3) == 'ringkasan' ? 'active' : '' }}"
                                        role="tab">Ringkasan</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                @if (request()->segment(2) == 'bulanan' && !request()->segment(3) ? 'umum' : request()->segment(3) == 'umum')
                                    <x-generalBulanan />
                                @elseif (request()->segment(3) == 'aktivitas')
                                    <x-aktivitas />
                                @elseif (request()->segment(3) == 'ringkasan')
                                    <x-ringkasan />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if (request()->segment(2) == 'bulanan' && !request()->segment(3) ? 'umum' : request()->segment(3) == 'umum')
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="report-section">
                                <div class="card">
                                    <div class="card-header">
                                        <img src="{{ asset('assets/icon/list-keluar.png') }}" width="20" height="20"
                                            alt="expense"> Pengeluaran
                                    </div>
                                    <div class="card-body">
                                        <div class="exin" id="ex_exin">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <!-- Data pengeluaran akan diisi di sini oleh JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <div class="report-section">
                                <div class="card">
                                    <div class="card-header">
                                        <img src="{{ asset('assets/icon/list-masuk.png') }}" width="20" height="20"
                                            alt="income"> Pemasukan
                                    </div>
                                    <div class="card-body">
                                        <div class="exin" id="in_exin">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    <!-- Data pemasukan akan diisi di sini oleh JavaScript -->
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
@push('custom-js')
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/laporan/bulanan/laporan.js') }}"></script>
@endpush
