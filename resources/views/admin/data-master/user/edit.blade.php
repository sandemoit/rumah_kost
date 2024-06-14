@push('custom-button')
    <div class="col-sm-6">
        <div class="float-end">
            <a class="btn btn-dark" href="javascript:history.back()"><i class="bi bi-arrow-left"></i> {{ __('Kembali') }}</a>
        </div>
    </div>
@endpush

@extends('layouts.app')

@section('content')
    <main class="app-main">
        <!--begin::App Content Header-->
        @include('layouts.heading')
        <div class="app-content">
            <!--begin::Container-->
            <div class="container-fluid">
                <!--begin::Row-->
                <div class="row g-4">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <!--begin::Quick Example-->
                        <div class="card card-primary card-outline mb-4">
                            <!--begin::Header-->
                            <div class="card-header">
                                <div class="card-title">Form Edit Pengguna</div>
                            </div>
                            <!--end::Header-->
                            <!--begin::Form-->
                            <form action="{{ route('usermanajemen.update', $user->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="name" id="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', $user->name) }}">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input disabled type="email" class="form-control"
                                            value="{{ old('email', $user->email) }}">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="old_password" id="old_password"
                                            class="form-control @error('old_password') is-invalid @enderror">
                                        @error('old_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <p class="text-danger">Kosongkan jika tidak ingin mengganti password</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Konfirmasi Password</label>
                                        <input type="password" name="new_password" id="new_password"
                                            class="form-control @error('new_password') is-invalid @enderror">
                                        @error('new_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Level Pengguna</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option selected disabled>Pilih Level User...</option>
                                            <option value="admin"
                                                {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="karyawan"
                                                {{ old('role', $user->role) == 'karyawan' ? 'selected' : '' }}>Karyawan
                                            </option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <!--end::Body-->

                                <!--begin::Footer-->
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                                <!--end::Footer-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Quick Example-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div>
            <!--end::Container-->
        </div>
    </main>
    <!--end::App Main-->
@endsection
