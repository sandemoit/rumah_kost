@push('load-css')
    <link rel="stylesheet" href="{{ asset('assets/css/laporan.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        <div class="app-content-header"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">Semua Buku Kas</h3>
                        <p>{{ __($pageTitle) }}</p>
                    </div>
                    @stack('custom-button')
                </div> <!--end::Row-->
            </div> <!--end::Container-->
        </div>
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row">
                    <div class="col-md-6 reportSelect">
                        <div class="form-group">
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
                        <input name="lap_tgl" type="text" class="lap_tgl datepicker hasDatepicker mt-4" id="lap_tgl"
                            value="{{ dateIndo(\Carbon\Carbon::now()->format('Y-m-d')) }}" size="15"
                            title="Klik untuk mengganti tanggal" onchange="changedate()">
                    </div>
                </div>

                <!-- Report Section -->
                <div class="report-section">
                    <div class="card">
                        <div class="card-header">
                            <i class="bi bi-journal-text"></i> Semua Buku Kas
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-pills" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a href="{{ route('laporan.harian.umum') }}"
                                        class="nav-link {{ request()->segment(2) == 'harian' && !request()->segment(3) ? 'active' : (request()->segment(3) == 'umum' ? 'active' : '') }}"
                                        role="tab">Umum</a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a href="{{ route('laporan.harian.aktivitas') }}"
                                        class="nav-link {{ request()->segment(3) == 'aktivitas' ? 'active' : '' }}"
                                        role="tab">Aktivitas</a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                @if (request()->segment(2) == 'harian' && !request()->segment(3) ? 'umum' : request()->segment(3) == 'umum')
                                    <x-general />
                                @elseif (request()->segment(3) == 'aktivitas')
                                    <x-aktivitas />
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @if (request()->segment(2) == 'harian' && !request()->segment(3) ? 'umum' : request()->segment(3) == 'umum')
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
                                                    <tr>
                                                        <td>Akasia-01</td>
                                                        <td class="right tdmatauang">Rp</td>
                                                        <td class="right tduang">200.000,00</td>
                                                    </tr>
                                                    <tr>
                                                        <td>ALL Akasia</td>
                                                        <td class="right tdmatauang">Rp</td>
                                                        <td class="right tduang">300.000,00</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="line">&nbsp;</td>
                                                        <td class="right tdmatauang line">Rp</td>
                                                        <td class="right tduang line">500.000,00</td>
                                                    </tr>
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
                                        <table class="table table-bordered">
                                            <tbody>
                                                <tr>
                                                    <td>Akasia-01</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">700.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>Akasia-02</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">650.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>Akasia-03</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">700.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>Akasia-04</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">650.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>Akasia-05</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">700.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>Akasia-06</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">650.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td>Akasia-07</td>
                                                    <td class="right tdmatauang">Rp</td>
                                                    <td class="right tduang">650.000,00</td>
                                                </tr>
                                                <tr>
                                                    <td class="line">&nbsp;</td>
                                                    <td class="right tdmatauang line">Rp</td>
                                                    <td class="right tduang line">4.700.000,00</td>
                                                </tr>
                                            </tbody>
                                        </table>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/chart.js') }}"></script>
    <script src="{{ asset('assets/js/laporan.js') }}"></script>
    <script src="{{ asset('assets/js/datepicker.js') }}"></script>
@endpush
