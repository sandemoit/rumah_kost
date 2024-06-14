@push('custom-button')
    <div class="col-sm-6">
        <div class="float-end">
            <a class="btn btn-primary" href="{{ route('usermanajemen.create') }}"><i class="bi bi-person-plus-fill"></i>
                {{ __('Tambah Pengguna') }}</a>
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
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $key)
                                            <tr class="align-middle">
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $key->name }}</td>
                                                <td>{{ $key->email }}</td>
                                                <td>
                                                    @if ($key->role == 'admin')
                                                        <span class="badge bg-success">Admin</span>
                                                    @elseif ($key->role == 'karyawan')
                                                        <span class="badge bg-primary">Karyawan</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('usermanajemen.edit', $key->id) }}"
                                                        class="btn btn-primary"><i class="bi bi-pencil-square"></i> Edit</a>
                                                    <a href="{{ route('usermanajemen.destroy', $key->id) }}"
                                                        class="btn btn-danger" onclick="confirmDelete(event, this)"><i
                                                            class="bi bi-trash"></i> Hapus</a>
                                                </td>
                                            </tr>
                                        @endforeach
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
@endsection
