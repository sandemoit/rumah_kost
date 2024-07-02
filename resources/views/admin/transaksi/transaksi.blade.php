@push('load-css')
    <link rel="stylesheet" href="{{ asset('assets/css/transaksi.css') }}">
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        <div class="app-content-header"> <!--begin::Container-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">{{ __($pageTitle) }}</h3>
                        <p>{{ __($keterangan) }}</p>
                    </div>
                    <div class="col-sm-6 text-end">
                        <strong>Saldo</strong>
                        <h3 class="mb-0" id="saldoKontrakan">Loading...</h3>
                        <span class="smallsaldo">Semua Buku Kas: Rp 10.350.000,00</span>
                    </div>
                </div> <!--end::Row-->
            </div>
        </div> <!--end::Container-->
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row bukutop">
                    <div class="col-sm-6">
                        <div class="bulannav">
                            <a href="#" class="bulan_nav_left" title="Bulan sebelumnya">&nbsp;</a>
                            <a href="#" class="bulan_nav_right" title="Bulan selanjutnya">&nbsp;</a>
                            <div class="bulankas">
                                <select class="selectfilter" name="month" id="nav_month"
                                    onchange="change_monthyear_link()" style="max-width: 115px;">
                                    <?php $now = date('m'); ?>
                                    <option value="1" {{ $now == '01' ? 'selected' : '' }}>Januari</option>
                                    <option value="2" {{ $now == '02' ? 'selected' : '' }}>Februari</option>
                                    <option value="3" {{ $now == '03' ? 'selected' : '' }}>Maret</option>
                                    <option value="4" {{ $now == '04' ? 'selected' : '' }}>April</option>
                                    <option value="5" {{ $now == '05' ? 'selected' : '' }}>Mei</option>
                                    <option value="6" {{ $now == '06' ? 'selected' : '' }}>Juni</option>
                                    <option value="7" {{ $now == '07' ? 'selected' : '' }}>Juli</option>
                                    <option value="8" {{ $now == '08' ? 'selected' : '' }}>Agustus</option>
                                    <option value="9" {{ $now == '09' ? 'selected' : '' }}>September</option>
                                    <option value="10" {{ $now == '10' ? 'selected' : '' }}>Oktober</option>
                                    <option value="11" {{ $now == '11' ? 'selected' : '' }}>November</option>
                                    <option value="12" {{ $now == '12' ? 'selected' : '' }}>Desember</option>
                                </select>
                                &nbsp;
                                <select class="selectfilter" name="year" id="nav_year"
                                    onchange="change_monthyear_link()" style="max-width: 72px;">
                                    <?php
                                    $now = date('Y');
                                    for ($i = $now - 5; $i <= $now; $i++) {
                                        echo '<option value="' . $i . '" ' . ($i == $now ? 'selected' : '') . '>' . $i . '</option>';
                                    }
                                    ?>
                                </select>
                                &nbsp;
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 text-end kastool" id="catatkas">
                        <span class="kasbutton" id="pengeluaran" onclick="catat_out()">Catat Pengeluaran</span>
                        <span class="kasbutton" id="pemasukan" onclick="catat_in()">Catat Pemasukan</span>
                    </div>
                </div>

                <div id="formContainer">
                    <form id="formPemasukan" style="display: none;">
                        @csrf
                        <h3>Pemasukan</h3>
                        <div class="form-group">
                            <label for="tanggalTerima">Tanggal Terima:</label>
                            <input type="date" class="form-control" id="tanggalTerima" name="tanggalTerima"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="kamarPemasukan">Kamar:</label>
                            <select class="form-select" id="kamarPemasukan" name="kamarPemasukan">
                                <option selected disabled>Pilih kamar...</option>
                                @foreach ($kamar as $room)
                                    <option value="{{ $room->id }}">{{ $room->nama_kamar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="periodeSewa">Periode Sewa:</label>
                            <input type="text" class="form-control" id="periodeSewa" name="periodeSewa" disabled
                                readonly>
                            <input type="hidden" id="tahunSewa" name="tahunSewa" value="{{ date('Y') }}">
                        </div>
                        <div class="form-group">
                            <label for="nilaiSewa">Nilai Sewa:</label>
                            <input type="text" class="form-control" id="nilaiSewa" name="nilaiSewa" disabled readonly>
                        </div>
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi:</label>
                            <input type="text" class="form-control" id="deskripsi" name="deskripsi" required>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-secondary"
                                onclick="$('#formContainer').slideUp()">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                            <a href="#" class="btn btn-danger d-none" id="deleteButton">Hapus</a>
                        </div>
                    </form>


                    <form id="formPengeluaran" style="display: none;">
                        <h3>Pengeluaran</h3>
                        <div class="form-group">
                            <label for="tanggalPengeluaran">Tanggal Pengeluaran:</label>
                            <input type="date" class="form-control" id="tanggalPengeluaran" name="tanggalPengeluaran"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="kamarPengeluaran">Kamar:</label>
                            <select class="form-select" id="kamarPengeluaran" name="kamarPengeluaran">
                                <option value="all">All</option>
                                @foreach ($kamar as $key)
                                    <option value="{{ $key->id }}">{{ $key->nama_kamar }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="nominalPengeluaran">Nominal:</label>
                            <input type="number" class="form-control" id="nominalPengeluaran"
                                name="nominalPengeluaran">
                        </div>
                        <div class="form-group">
                            <label for="deskripsiPengeluaran">Deskripsi:</label>
                            <input type="text" class="form-control" id="deskripsiPengeluaran"
                                name="deskripsiPengeluaran" required>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-secondary"
                                onclick="$('#formContainer').slideUp()">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                            <a href="#" class="btn btn-danger d-none" id="deleteButton">Hapus</a>
                        </div>
                    </form>
                </div>
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <div class="card-tools">
                                    <form action="{{ route('penyewa') }}" method="GET">
                                        <div class="input-group input-group-sm" style="width: 150px;">
                                            <input type="text" name="search" id="search"
                                                value="{{ request('search') }}" class="form-control float-right"
                                                placeholder="Search" autocomplete="off" autofocus>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-search"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="table-container bukutengah">
                                <table class="transaksi-table">
                                    <thead>
                                        <tr>
                                            <th class="center">Tipe</th>
                                            <th class="center">Tanggal</th>
                                            <th class="center">Kamar</th>
                                            <th class="center deskripsikas-column">Deskripsi</th>
                                            <th class="right">Nominal</th>
                                            <th class="right saldo-column">Saldo</th>
                                            <th class="center">Edit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($transaksiList as $transaksi)
                                            <tr class="list{{ $transaksi->tipe }}" id="list_{{ $transaksi->id }}">
                                                <td class="tipekas" data-label="Tipe">
                                                    @if ($transaksi->tipe == 'masuk')
                                                        <img src="{{ asset('assets/icon/list-masuk.png') }}"
                                                            alt="income" title="Income">
                                                    @else
                                                        <img src="{{ asset('assets/icon/list-keluar.png') }}"
                                                            alt="expense" title="Expense">
                                                    @endif
                                                </td>
                                                <td class="tanggalkas center" data-label="Tanggal">
                                                    <span class="full-date">
                                                        @if ($transaksi->tipe == 'masuk' && $transaksi->transaksiMasuk)
                                                            {{ tanggal($transaksi->transaksiMasuk->tanggal_transaksi) }}
                                                        @elseif ($transaksi->tipe == 'keluar' && $transaksi->transaksiKeluar)
                                                            {{ tanggal($transaksi->transaksiKeluar->tanggal_transaksi) }}
                                                        @endif
                                                    </span>
                                                    <span class="short-date">
                                                        @if ($transaksi->tipe == 'masuk' && $transaksi->transaksiMasuk)
                                                            {{ hari($transaksi->transaksiMasuk->tanggal_transaksi) }}
                                                        @elseif ($transaksi->tipe == 'keluar' && $transaksi->transaksiKeluar)
                                                            {{ hari($transaksi->transaksiKeluar->tanggal_transaksi) }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td class="kamarkas" data-label="Kamar">
                                                    {{ $transaksi->kamar->nama_kamar }}</td>
                                                <td class="deskripsikas" data-label="Deskripsi">
                                                    @if ($transaksi->tipe == 'masuk' && $transaksi->transaksiMasuk)
                                                        {{ $transaksi->transaksiMasuk->deskripsi }}
                                                    @elseif ($transaksi->tipe == 'keluar' && $transaksi->transaksiKeluar)
                                                        {{ $transaksi->transaksiKeluar->deskripsi }}
                                                    @endif
                                                    <br><small>Dibuat oleh {{ $transaksi->user->name ?? 'Admin' }}</small>
                                                </td>
                                                <td class="nominalkas right" data-label="Nominal">
                                                    {{ rupiah($transaksi->nominal) }}</td>
                                                <td class="nominalkas right saldo-column" data-label="Saldo">
                                                    {{ rupiah($transaksi->saldo) }}</td>
                                                <td class="center editkas">
                                                    <input type="hidden" class="transaksi-id"
                                                        value="{{ $transaksi->id }}">
                                                    <span class="editkas" title="Edit atau hapus" alt="Edit"
                                                        data-transaksi-id="{{ $transaksi->id }}"
                                                        data-tipe="{{ $transaksi->tipe }}"
                                                        data-tanggal="{{ $transaksi->transaksiMasuk ? $transaksi->transaksiMasuk->tanggal_transaksi : $transaksi->transaksiKeluar->tanggal_transaksi }}"
                                                        data-kamar-id="{{ $transaksi->id_kamar }}"
                                                        data-deskripsi="{{ $transaksi->transaksiMasuk ? $transaksi->transaksiMasuk->deskripsi : $transaksi->transaksiKeluar->deskripsi }}"
                                                        data-nominal="{{ $transaksi->nominal }}"
                                                        onclick="edit_exin(this)"><i class="bi bi-gear"></i>
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ditemukan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('custom-js')
    <script src=" {{ asset('assets/js/transaksi.js') }}"></script>
@endpush
