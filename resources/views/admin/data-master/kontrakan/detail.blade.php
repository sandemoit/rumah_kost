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
                                <h3 class="card-title">Kamar Table</h3>
                            </div> <!-- /.card-header -->
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th style="width: 10px">No</th>
                                            <th>Nama Kamar</th>
                                            <th>Keterangan</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($kamar) > 0)
                                            @foreach ($kamar as $key)
                                                <tr class="align-middle">
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $key->nama_kamar }}</td>
                                                    <td>{{ $key->keterangan }}</td>
                                                    <td>
                                                        <a href="javascript:void(0)" class="btn btn-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editKamar_{{ $key->id }}"><i
                                                                class="bi bi-pencil-square"></i>
                                                            Edit</a>
                                                        <a href="{{ route('kontrakan.destroy_kamar', [$kontrakan->nama_kontrakan, $key->id]) }}"
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
    @foreach ($kamar as $key)
        <div class="modal fade" id="editKamar_{{ $key->id }}" tabindex="-1" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Edit Kamar</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('kontrakan.update_kamar', [$kontrakan->nama_kontrakan, $key->id]) }}"
                            method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-group mb-3">
                                <label for="nama_kamar">Nama Kamar</label>
                                <input type="text" class="form-control" id="nama_kamar" name="nama_kamar"
                                    value="{{ old('nama_kamar', $key->nama_kamar) }}">
                                @error('nama_kamar')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="keterangan">Keterangan</label>
                                <textarea class="form-control" id="keterangan" name="keterangan">{{ old('keterangan', $key->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="text-danger">{{ $message }}</div>
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
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Kamar</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('kontrakan.store_kamar', Str::slug($kontrakan->nama_kontrakan)) }}"
                        method="POST">
                        @csrf
                        <input type="hidden" value="{{ $kontrakan->id }}" name="id_kontrakan" id="id_kontrakan">
                        <div class="form-group mb-3">
                            <label for="nama_kamar" class="form-label">Nama Kamar</label>
                            <input type="text" id="nama_kamar" name="nama_kamar" class="form-control"
                                aria-describedby="kontrakanHelpBlock" value="{{ old('nama_kamar') }}">
                            @error('nama_kamar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group mb-3">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <input type="text" id="keterangan" name="keterangan" class="form-control"
                                aria-describedby="keteranganHelpBlock" value="{{ old('keterangan') }}">
                            @error('keterangan')
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
