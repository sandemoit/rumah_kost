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
                    <div class="col-12">
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
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($penyewa) > 0)
                                            @foreach ($penyewa as $key)
                                                <tr class="align-middle">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $key->nama_penyewa }}</td>
                                                    <td>{{ tanggal($key->tanggal_masuk) }}</td>
                                                    <td><a href="tel:{{ $key->nomor_wa }}">{{ $key->nomor_wa }}</a></td>
                                                    <td>{{ $key->kamar['nama_kamar'] }}</td>
                                                    <td>
                                                        @if ($key->status == 'aktif')
                                                            <span class="badge bg-success">Aktif</span>
                                                        @elseif ($key->status == 'tidak_aktif')
                                                            <span class="badge bg-danger">Nonaktif</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="btn btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editKamar_{{ $key->id }}"><i
                                                                class="bi bi-pencil-square"></i>
                                                            Edit</a>
                                                        <a href="{{ route('penyewa.destroy', $key->id) }}"
                                                            class="btn btn-danger" onclick="confirmDelete(event, this)"><i
                                                                class="bi bi-trash"></i>
                                                            Hapus</a>
                                                        <a href="#" class="btn btn-success" target="_blank"><i
                                                                class="bi bi-whatsapp"></i> Kirim Tagihan</a>
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

    <!-- Modal Edit-->
    @foreach ($penyewa as $key)
        <div class="modal fade" id="editKamar_{{ $key->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Edit penyewa</h1>
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
                            </div>
                            @error('nama_penyewa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-group mb-3">
                                <label for="nomor_wa" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control @error('nomor_wa') is-invalid @enderror"
                                    id="nomor_wa" name="nomor_wa" value="{{ $key->nomor_wa }}" required>
                            </div>
                            @error('nomor_wa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div class="form-group mb-3">
                                <label for="tanggal_masuk">Tanggal Masuk</label>
                                <input type="date" class="form-control @error('tanggal_masuk') is-invalid @enderror"
                                    id="tanggal_masuk" name="tanggal_masuk" value="{{ $key->tanggal_masuk }}" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="id_kontrakan" class="form-label">Kontrakan</label>
                                <select class="form-select @error('id_kontrakans') is-invalid @enderror" id="id_kontrakans"
                                    name="id_kontrakan" required>
                                    <option disabled {{ $key->id ? '' : 'selected' }}>Pilih Kontrakan</option>
                                    @foreach ($kontrakan as $item)
                                        <option value="{{ $item->id }}"
                                            {{ $item->id == $key->id ? 'selected' : '' }}>
                                            {{ $item->nama_kontrakan }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('id_kontrakans')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="kamars" class="form-label">Kamar</label>
                                <div>
                                    <select class="form-select @error('id_kamar') is-invalid @enderror" id="kamars"
                                        name="id_kamar" required>
                                        <option disabled {{ $key->id ? '' : 'selected' }}>Pilih Kamar
                                        </option>
                                        @foreach ($kamar as $item)
                                            <option value="{{ $item->id }}"
                                                {{ $item->id == $key->id ? 'selected' : '' }}>
                                                {{ $item->nama_kamar }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
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
                            <select class="form-select @error('id_kontrakan') is-invalid @enderror" id="id_kontrakan"
                                name="id_kontrakan" required>
                                <option selected disabled>Pilih Kontrakan</option>
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
                            <div id="pilihan-kamar-container">
                                <select class="form-select @error('kamar') is-invalid @enderror" id="id_kamar"
                                    name="id_kamar" required style="display: none;">
                                </select>
                            </div>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const kontrakanSelect = document.getElementById('id_kontrakan');
            const pilihanKamarContainer = $('#pilihan-kamar-container');
            const pilihanKamar = $('#id_kamar');

            // Sembunyikan dropdown pilihan kamar saat halaman dimuat
            pilihanKamar.hide();

            // Menangani perubahan pada dropdown Kontrakan
            kontrakanSelect.addEventListener('change', function() {
                const kontrakanId = this.value;

                // Kirim permintaan Ajax untuk mengambil data kamar berdasarkan kontrakanId
                fetch(`/get-kamar/${kontrakanId}`)
                    .then(response => response.json())
                    .then(data => {
                        // Kosongkan dropdown sebelumnya
                        pilihanKamar.empty();

                        // Tambahkan opsi kamar baru jika ada data yang diterima
                        if (data.length > 0) {
                            data.forEach(kamar => {
                                const option = document.createElement('option');
                                option.value = kamar.id;
                                option.textContent = kamar.nama_kamar;
                                pilihanKamar.append(option);
                            });

                            // Tampilkan pilihan kamar dengan efek slide down
                            pilihanKamar.slideDown();
                        } else {
                            // Jika tidak ada data, tambahkan opsi default
                            const option = document.createElement('option');
                            option.textContent = 'Tidak ada kamar tersedia';
                            pilihanKamar.append(option);

                            // Tampilkan pilihan kamar dengan efek slide down
                            pilihanKamar.slideDown();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
            });
        });
    </script>
@endpush
