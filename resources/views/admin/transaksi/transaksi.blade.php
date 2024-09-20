@push('load-css')
    <link rel="stylesheet" href="{{ asset('assets/css/transaksi.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
    <link rel="stylesheet" href="{{ asset('assets/css/select2-bootstrap-5-theme.min.css') }}" />
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
                        <span class="smallsaldo">Saldo 1 Tahun: <span id="totalSaldo">Loading...</span></span>
                    </div>
                </div> <!--end::Row-->
            </div>
        </div> <!--end::Container-->
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row bukutop">
                    <div class="col-lg-6 col-md-3 col-sm-3">
                        <div class="bulannav">
                            <a href="#" class="bulan_nav_left" id="bulan_nav_left" title="Bulan sebelumnya">&nbsp;</a>
                            <a href="#" class="bulan_nav_right" id="bulan_nav_right"
                                title="Bulan selanjutnya">&nbsp;</a>
                            <form action="{{ route('transaksi.kontrakan', $code_kontrakan) }}" method="GET"
                                id="filterForm">
                                <div class="bulankas">
                                    <select class="selectfilter" name="month" id="nav_month" onchange="submitFilter()"
                                        style="max-width: 115px;">
                                        @php
                                            $nowMonth = request()->input('month', date('m'));
                                        @endphp
                                        <option value="all" {{ $nowMonth == 'all' ? 'selected' : '' }}>All</option>
                                        <option value="1" {{ $nowMonth == '01' ? 'selected' : '' }}>Januari</option>
                                        <option value="2" {{ $nowMonth == '02' ? 'selected' : '' }}>Februari</option>
                                        <option value="3" {{ $nowMonth == '03' ? 'selected' : '' }}>Maret</option>
                                        <option value="4" {{ $nowMonth == '04' ? 'selected' : '' }}>April</option>
                                        <option value="5" {{ $nowMonth == '05' ? 'selected' : '' }}>Mei</option>
                                        <option value="6" {{ $nowMonth == '06' ? 'selected' : '' }}>Juni</option>
                                        <option value="7" {{ $nowMonth == '07' ? 'selected' : '' }}>Juli</option>
                                        <option value="8" {{ $nowMonth == '08' ? 'selected' : '' }}>Agustus</option>
                                        <option value="9" {{ $nowMonth == '09' ? 'selected' : '' }}>September</option>
                                        <option value="10" {{ $nowMonth == '10' ? 'selected' : '' }}>Oktober</option>
                                        <option value="11" {{ $nowMonth == '11' ? 'selected' : '' }}>November</option>
                                        <option value="12" {{ $nowMonth == '12' ? 'selected' : '' }}>Desember</option>
                                    </select>
                                    &nbsp;
                                    <select class="selectfilter" name="year" id="nav_year" onchange="submitFilter()"
                                        style="max-width: 72px;">
                                        <?php
                                        $nowYear = request()->input('year', date('Y'));
                                        $currentYear = date('Y');
                                        for ($i = $currentYear - 5; $i <= $currentYear; $i++) {
                                            echo '<option value="' . $i . '" ' . ($i == $nowYear ? 'selected' : '') . '>' . $i . '</option>';
                                        }
                                        ?>
                                    </select>
                                    &nbsp;
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-9 col-sm-9 text-end kastool" id="catatkas">
                        <span class="kasbutton" id="pengeluaran" onclick="catat_out()">Catat Pengeluaran</span>
                        <span class="kasbutton" id="pemasukan" onclick="catat_in()">Catat Pemasukan</span>
                        <span class="kasbutton" id="tunggakan" onclick="catat_tunggakan()">Catat Tunggakan</span>
                    </div>
                </div>

                <div id="formContainer">
                    <form id="formTunggakan">
                        @csrf
                        <h3 class="text-dark">Tunggakan</h3>
                        <div class="form-group">
                            <label for="tanggalTunggakan">Tanggal Terima:</label>
                            <input type="date" class="form-control" id="tanggalTunggakan" name="tanggalTunggakan"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="kamarTunggakan">Kamar:</label>
                            <select class="form-select" id="kamarTunggakan" name="kamarTunggakan">
                                <option selected disabled>Pilih kamar...</option>
                                @foreach ($kamarTunggakan as $room)
                                    <option value="{{ $room->id }}">
                                        {{ $room->nama_kamar . ' ' . $room->nama_penyewa }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="codeKontrakan" name="codeKontrakan">
                        </div>
                        <div class="form-group">
                            <label for="periodeTunggakanDeskripsi">Periode Sewa:</label>
                            <input type="text" class="form-control" id="periodeTunggakanDeskripsi"
                                name="periodeTunggakanDeskripsi" disabled readonly>
                            <input type="hidden" id="periodeTunggakan" name="periodeTunggakan">
                        </div>
                        <div class="form-group">
                            <label for="nilaiTunggakan">Nilai Sewa:</label>
                            <input type="text" class="form-control" id="nilaiTunggakan" name="nilaiTunggakan" disabled
                                readonly>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-secondary"
                                onclick="$('#formContainer').slideUp()">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                        </div>
                    </form>

                    <form id="formPemasukan">
                        @csrf
                        <input type="hidden" id="transaksiId" name="transaksiId">
                        <h3 class="text-success">Pemasukan</h3>
                        <div class="form-group">
                            <label for="tanggalTerima">Tanggal Terima:</label>
                            <input type="date" class="form-control" id="tanggalTerima" name="tanggalTerima">
                        </div>
                        <div class="form-group">
                            <label for="kamarPemasukan">Kamar:</label>
                            <select class="form-select" id="kamarPemasukan" name="kamarPemasukan">
                                <option selected disabled>Pilih kamar...</option>
                                @foreach ($kamar as $room)
                                    <option value="{{ $room->id }}">{{ $room->nama_kamar }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="codeKontrakan" name="codeKontrakan">
                        </div>
                        <div class="form-group">
                            <label for="periodeDeskripsi">Periode Sewa:</label>
                            <input type="text" class="form-control" id="periodeDeskripsi" name="periodeDeskripsi"
                                disabled readonly>
                            <input type="hidden" class="form-control" id="periodeSewa" name="periodeSewa">
                        </div>
                        <div class="form-group">
                            <label for="nilaiSewa">Nilai Sewa:</label>
                            <input type="text" class="form-control" id="nilaiSewa" name="nilaiSewa" disabled
                                readonly>
                        </div>
                        <div class="form-buttons">
                            <button type="button" class="btn btn-secondary"
                                onclick="$('#formContainer').slideUp()">Batal</button>
                            <button type="submit" class="btn btn-success">Simpan</button>
                            {{-- <a href="#" onclick="confirmDelete(event, this)" class="btn btn-danger d-none"
                                id="deleteButtonPemasukan">Hapus</a> --}}
                        </div>
                    </form>

                    <form id="formPengeluaran">
                        @csrf
                        <input type="hidden" id="transaksiId" name="transaksiId">
                        <input type="hidden" id="saldo" name="saldo">
                        <h3 class="text-danger">Pengeluaran</h3>
                        <div class="form-group">
                            <label for="tanggalPengeluaran">Tanggal Pengeluaran:</label>
                            <input type="date" class="form-control" id="tanggalPengeluaran" name="tanggalPengeluaran"
                                value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="kamarPengeluaran">Kamar:</label>
                            <select class="form-select multiple" multiple id="kamarPengeluaran" name="kamarPengeluaran"
                                data-placeholder="Pilih kamar...">
                                <option value="all">All</option>
                                @foreach ($kamar as $key)
                                    <option value="{{ $key->id }}">{{ $key->nama_kamar }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="codeKontrakanKeluar" name="codeKontrakanKeluar">
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
                            {{-- <a href="#" onclick="confirmDelete(event, this)" class="btn btn-danger d-none"
                                id="deleteButtonPengeluaran">Hapus</a> --}}
                        </div>
                    </form>
                </div>

                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <div style="float: left;" class="d-flex">
                                    <label for="per_page">Tampilkan </label>
                                    <form method="GET" action="{{ url()->current() }}" class="form-inline">
                                        <select name="per_page" id="per_page" class="form-select form-select-sm mx-2"
                                            onchange="this.form.submit()">
                                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25
                                            </option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50
                                            </option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100
                                            </option>
                                        </select>

                                        {{-- Menjaga query parameter lainnya --}}
                                        @foreach (request()->except('per_page', 'page') as $key => $value)
                                            <input type="hidden" name="{{ $key }}"
                                                value="{{ $value }}">
                                        @endforeach
                                    </form>
                                    <label for="per_page" style="margin-left: 1rem;">activitas</label>
                                </div>
                                <div class="card-tools">
                                    <form action="{{ route('transaksi.kontrakan', $code_kontrakan) }}" method="GET">
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
                                            {{-- <th class="right saldo-column">Saldo</th> --}}
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
                                                    {{ $transaksi->nama_kamar }}</td>
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
                                                {{-- <td class="nominalkas right saldo-column" data-label="Saldo">
                                                    {{ rupiah($transaksi->saldo) }}</td> --}}
                                                <td class="center editkas">
                                                    <input type="hidden" id="transaksi-id"
                                                        value="{{ $transaksi->id }}">
                                                    <span class="btn-editkas" title="Edit atau hapus" alt="Edit"
                                                        data-transaksi-id="{{ $transaksi->id }}"
                                                        data-tipe="{{ $transaksi->tipe }}"
                                                        data-tanggal="{{ $transaksi->transaksiMasuk ? $transaksi->transaksiMasuk->tanggal_transaksi : $transaksi->transaksiKeluar->tanggal_transaksi }}"
                                                        data-kamar-id="{{ $transaksi->id_kamar }}"
                                                        data-deskripsi="{{ $transaksi->transaksiMasuk ? $transaksi->transaksiMasuk->deskripsi : $transaksi->transaksiKeluar->deskripsi }}"
                                                        data-nominal="{{ nominal($transaksi->nominal) }}"
                                                        data-periode-sewa="{{ $transaksi->transaksiMasuk ? $transaksi->transaksiMasuk->periode_sewa : $transaksi->transaksiKeluar->periode_sewa }}"
                                                        onclick="edit_exin(this)"><i class="bi bi-gear"></i>
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center">Tidak ditemukan</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination Links -->
                            @if ($transaksiList->hasPages() || $transaksiList->total() > 0)
                                <div class="card-footer clearfix">
                                    {{ $transaksiList->appends(request()->query())->links() }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('custom-js')
    <script src=" {{ asset('assets/js/transaksi.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
@endpush
