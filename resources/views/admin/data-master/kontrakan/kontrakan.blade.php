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
                    <div class="col-12">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h3 class="card-title">User Table</h3>
                            </div> <!-- /.card-header -->
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px">No</th>
                                            <th>Nama Kontrakan</th>
                                            <th>Alamat Kontrakan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($kontrakan) > 0)
                                            @foreach ($kontrakan as $key)
                                                <tr class="align-middle">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $key->nama_kontrakan }}</td>
                                                    <td>{{ $key->alamat_kontrakan }}</td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="btn btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editKontrakan_{{ $key->id }}"><i
                                                                class="bi bi-pencil-square"></i>
                                                            Edit</a>
                                                        <a href="{{ route('kontrakan.destroy', $key->id) }}"
                                                            class="btn btn-danger" onclick="confirmDelete(event, this)"><i
                                                                class="bi bi-trash"></i>
                                                            Hapus</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="4" class="text-center">Tidak ada data kontrakan</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div> <!-- /.card-body -->
                            <div class="card-footer clearfix">
                                <ul class="pagination pagination-sm m-0 float-end">
                                    <li class="page-item"> <a class="page-link" href="#">&laquo;</a> </li>
                                    <li class="page-item"> <a class="page-link" href="#">1</a> </li>
                                    <li class="page-item"> <a class="page-link" href="#">2</a> </li>
                                    <li class="page-item"> <a class="page-link" href="#">3</a> </li>
                                    <li class="page-item"> <a class="page-link" href="#">&raquo;</a> </li>
                                </ul>
                            </div>
                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
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
