@push('custom-button')
    <div class="col-sm-6">
        <div class="float-end">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahKontrakan"><i
                    class="bi bi-person-plus-fill"></i> {{ __('Tambah Kontrakan') }}</button>
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
                    @if (count($kontrakan) == 0)
                        <div class="alert alert-warning">Belum ada data kontrakan</div>
                    @else
                        @foreach ($kontrakan as $key)
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-primary">
                                    <div class="inner">
                                        <h3 class="text-white">{{ $key->nama_kontrakan }}</h3>
                                        <p class="text-white">{{ $key->count_kamar }} Pintu</p>
                                    </div>
                                    <div class="icon">
                                        <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path
                                                d="M3 13h1v7c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a.999.999 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4zm2-15.586 6 6V15l.001 5H16v-5c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v5H6v-9.586l6-6z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div class="small-box-footer">
                                        <a href="{{ route('kontrakan.detail', Str::slug($key->code_kontrakan)) }}"
                                            class="btn btn-sm btn-primary me-2">
                                            kamar <i class="bi bi-arrow-right"></i>
                                        </a>
                                        <a href="javascript:void(0)" data-bs-toggle="modal"
                                            data-bs-target="#editKontrakan_{{ $key->id }}"
                                            class="btn btn-sm btn-warning me-2">
                                            Ubah <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="{{ route('kontrakan.destroy', $key->id) }}" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete(event, this)">
                                            Hapus <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div> <!--end::Row-->
            </div> <!--end::Container-->
        </div>
    </main> <!--end::App Main--> <!--begin::Footer-->

    <!-- Modal Edit-->
    @foreach ($kontrakan as $key)
        <div class="modal fade" id="editKontrakan_{{ $key->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Kontrakan</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('kontrakan.update', $key->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-3">
                                <label for="namaKontrakan" class="form-label">Nama Kontrakan</label>
                                <input type="text" id="namaKontrakan" name="namaKontrakan" class="form-control"
                                    aria-describedby="kontrakanHelpBlock"
                                    value="{{ $key->nama_kontrakan, old('namaKontrakan') }}">
                                @error('namaKontrakan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-3">
                                <label for="alamatKontrakan" class="form-label">Alamat Kontrakan</label>
                                <input type="text" id="alamatKontrakan" name="alamatKontrakan" class="form-control"
                                    aria-describedby="alamatHelpBlock"
                                    value="{{ $key->alamat_kontrakan, old('alamatKontrakan') }}">
                                @error('alamatKontrakan')
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
    <div class="modal fade" id="tambahKontrakan" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Kontrakan</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kontrakan.store') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="namaKontrakan" class="form-label">Nama Kontrakan</label>
                            <input type="text" id="namaKontrakan" name="namaKontrakan" class="form-control"
                                aria-describedby="kontrakanHelpBlock" value="{{ old('namaKontrakan') }}">
                            @error('namaKontrakan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="alamatKontrakan" class="form-label">Alamat Kontrakan</label>
                            <input type="text" id="alamatKontrakan" name="alamatKontrakan" class="form-control"
                                aria-describedby="alamatHelpBlock" value="{{ old('alamatKontrakan') }}">
                            @error('alamatKontrakan')
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
@endsection
