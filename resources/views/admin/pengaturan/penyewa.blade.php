@push('custom-button')
    <div class="col-sm-6">
        <div class="float-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahPenyewa"><i
                    class="bi bi-person-plus-fill"></i> {{ __('Tambah Penyewa') }}</button>
        </div>
    </div>
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">{{ __($pageTitle) }} Table</h3>
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
                            </div> <!-- /.card-header -->
                            <div class="table-responsive card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px">No</th>
                                            <th>Nama</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Nomor WA</th>
                                            <th>Kamar</th>
                                            <th class="text-center">Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($penyewa) > 0)
                                            @foreach ($penyewa as $key)
                                                <tr class="align-middle bg-black">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $key->nama_penyewa }}</td>
                                                    <td>{{ tanggal($key->tanggal_masuk) }}</td>
                                                    <td><a href="tel:{{ $key->nomor_wa }}">{{ $key->nomor_wa }}</a></td>
                                                    <td>{{ $key->kamar['nama_kamar'] }}</td>
                                                    <td class="text-center">
                                                        @if ($key->status == 'aktif')
                                                            <span class="badge bg-success">Aktif</span>
                                                        @elseif ($key->status == 'tidak_aktif')
                                                            <span class="badge bg-danger">Nonaktif</span>
                                                        @elseif ($key->status == 'putus_kontrak')
                                                            <span class="badge bg-dark">Putus Kontrak</span><br>
                                                            <span>{{ tanggal($key->tanggal_putus_kontrak) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <!-- Toggle Button for Mobile -->
                                                        <div class="action-toggle">
                                                            <button class="toggle-button btn btn-primary"
                                                                onclick="toggleActions({{ $key->id }})"><i
                                                                    class="bi bi-layers"></i></button>

                                                            <!-- Action Buttons Container -->
                                                            <div class="action-buttons"
                                                                id="action-buttons-{{ $key->id }}">
                                                                @if ($key->status != 'putus_kontrak')
                                                                    <a href="javascript:void(0)" class="btn-mobile"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#editPenyewa_{{ $key->id }}">
                                                                        <i class="bi bi-pencil-square"></i> Ubah
                                                                    </a>
                                                                    <a href="{{ route('penyewa.wa_tagihan', $key->id) }}"
                                                                        class="btn-mobile">
                                                                        <i class="bi bi-whatsapp"></i> Kirim Tagihan
                                                                    </a>
                                                                    <a href="{{ route('penyewa.putus_kontrak', $key->id) }}"
                                                                        class="btn-mobile"
                                                                        onclick="return confirm('Apakah anda yakin ingin putus kontrak?')">
                                                                        <i class="bi bi-x-circle"></i> Putus Kontrak
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <!-- Action Buttons for PC -->
                                                        <div class="aksi-button-pc">
                                                            @if ($key->status != 'putus_kontrak')
                                                                <a href="javascript:void(0)" class="btn btn-primary"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#editPenyewa_{{ $key->id }}">
                                                                    <i class="bi bi-pencil-square"></i> Ubah
                                                                </a>
                                                                <a href="{{ route('penyewa.wa_tagihan', $key->id) }}"
                                                                    class="btn btn-success">
                                                                    <i class="bi bi-whatsapp"></i> Kirim Tagihan
                                                                </a>
                                                                <a href="{{ route('penyewa.putus_kontrak', $key->id) }}"
                                                                    class="btn btn-dark"
                                                                    onclick="return confirm('Apakah anda yakin ingin putus kontrak?')">
                                                                    <i class="bi bi-x-circle"></i> Putus Kontrak
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center">Tidak ada data</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div> <!-- /.card-body -->

                            <!-- Pagination Links -->
                            <div class="card-footer clearfix">
                                {{ $penyewa->links() }}
                            </div>

                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
                </div> <!--end::Row-->
            </div> <!--end::Container-->
        </div>
    </main>

    <!-- Form modal for edit penyewa -->
    @foreach ($penyewa as $key)
        <div class="modal fade" id="editPenyewa_{{ $key->id }}" tabindex="-1"
            aria-labelledby="editPenyewaModalLabel{{ $key->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editPenyewaModalLabel{{ $key->id }}">Edit Penyewa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('penyewa.update', $key->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-3">
                                <label for="nama_penyewa" class="form-label">Nama Penyewa</label>
                                <input type="text" class="form-control @error('nama_penyewa') is-invalid @enderror"
                                    id="nama_penyewa" name="nama_penyewa" value="{{ $key->nama_penyewa }}" required>
                                @error('nama_penyewa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="nomor_wa" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control @error('nomor_wa') is-invalid @enderror"
                                    id="nomor_wa" name="nomor_wa" value="{{ $key->nomor_wa }}" required>
                                @error('nomor_wa')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="tanggal_masuk">Tanggal Masuk</label>
                                <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                    id="tanggal_masuk" name="tanggal_masuk" value="{{ $key->tanggal_masuk }}" required>
                                @error('tanggal_masuk')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="id_kontrakan" class="form-label">Kontrakan</label>
                                <select class="form-select @error('id_kontrakan') is-invalid @enderror" id="id_kontrakans"
                                    name="id_kontrakan" required>
                                    <option disabled>Pilih Kontrakan</option>
                                    @foreach ($kontrakan as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $item->id == $key->id_kontrakan ? 'selected' : '' }}>
                                            {{ $item->nama_kontrakan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_kontrakan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="kamars" class="form-label">Kamar</label>
                                <select class="form-select @error('id_kamar') is-invalid @enderror" id="kamars"
                                    name="id_kamar" required>
                                    <option disabled>Pilih Kamar</option>
                                    @foreach ($kamar->where('id_kontrakan', $key->id_kontrakan) as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $item->id == $key->id_kamar ? 'selected' : '' }}
                                            {{ in_array($item->id, $kamarTerisi) && $item->id != $key->id_kamar ? 'disabled' : '' }}>
                                            {{ $item->nama_kamar }}
                                            {{ in_array($item->id, $kamarTerisi) && $item->id != $key->id_kamar ? '(Terisi)' : '' }}
                                        </option>
                                    @endforeach
                                    @if ($kamar->where('id_kontrakan', $key->id_kontrakan)->isEmpty())
                                        <option disabled>Semua Kamar Sudah Penuh</option>
                                    @endif
                                </select>
                                @error('id_kamar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Modal -->
    <div class="modal fade" id="tambahPenyewa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah penyewa</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('penyewa.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="nama_penyewa" class="form-label">Nama Penyewa</label>
                            <input type="text" class="form-control @error('nama_penyewa') is-invalid @enderror"
                                id="nama_penyewa" name="nama_penyewa" value="{{ old('nama_penyewa') }}" required>
                        </div>
                        @error('nama_penyewa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-group mb-3">
                            <label for="nomor_wa" class="form-label">Nomor Telepon</label>
                            <input type="text" class="form-control @error('nomor_wa') is-invalid @enderror"
                                id="nomor_wa" name="nomor_wa" value="{{ old('nomor_wa') }}" required>
                        </div>
                        @error('nomor_wa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-group mb-3">
                            <label for="tanggal_masuk">Tanggal Masuk</label>
                            <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                id="tanggal_masuk" name="tanggal_masuk" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="id_kontrakan" class="form-label">Kontrakan</label>
                            <select class="form-select" id="id_kontrakan" name="id_kontrakan" required>
                                <option disabled selected>Pilih kontrakan</option>
                                @foreach ($kontrakan as $item)
                                    <option value="{{ $item->id }}">{{ $item->nama_kontrakan }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('id_kontrakan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="form-group mb-3">
                            <label for="kamar" class="form-label">Kamar</label>
                            <select class="form-select @error('kamar') is-invalid @enderror" id="id_kamar"
                                name="id_kamar" required style="display: none;">
                            </select>
                        </div>
                        @error('kamar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('custom-js')
    <script src="{{ asset('assets/js/penyewa.js') }}"></script>
@endpush
