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
                                <div class="card-tools">
                                    <form action="{{ route('usermanajemen') }}" method="GET">
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
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (count($users) > 0)
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
                                                            class="btn btn-primary"><i class="bi bi-pencil-square"></i>
                                                            Ubah</a>
                                                        <a href="{{ route('usermanajemen.destroy', $key->id) }}"
                                                            class="btn btn-danger" onclick="confirmDelete(event, this)"><i
                                                                class="bi bi-trash"></i> Hapus</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center">Tidak ada data kamar</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div> <!-- /.card-body -->

                            <!-- Pagination Links -->
                            <div class="card-footer clearfix">
                                {{ $users->links() }}
                            </div>
                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
                </div> <!--end::Row-->
            </div> <!--end::Container-->
        </div>
    </main> <!--end::App Main--> <!--begin::Footer-->
@endsection
