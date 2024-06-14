@push('custom-button')
    <div class="col-sm-6">
        <div class="float-end">
            <a class="btn btn-dark" href="javascript:history.back()"><i class="bi bi-arrow-left"></i>
                {{ __('Kembali') }}</a>
        </div>
    </div>
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row g-4"> <!--begin::Col-->
                    <div class="col-md-6"> <!--begin::Quick Example-->
                        <div class="card card-primary card-outline mb-4"> <!--begin::Header-->
                            <div class="card-header">
                                <div class="card-title">Form Input</div>
                            </div> <!--end::Header--> <!--begin::Form-->
                            <form action="{{ route('usermanajemen.store') }}" method="POST">
                                @csrf
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Nama Lengkap</label>
                                        <input type="text" name="name" id="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}">
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" id="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" id="password"
                                            class="form-control @error('password') is-invalid @enderror">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Level Pengguna</label>
                                        <select class="form-select" id="role" name="role" required>
                                            <option selected disabled>Pilih Level User...</option>
                                            <option value="admin">Admin</option>
                                            <option value="karyawan">Karyawan</option>
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
                        </div> <!--end::Quick Example--> <!--begin::Input Group-->
                    </div> <!--end::Col--> <!--begin::Col-->
                </div> <!--end::Row-->
            </div> <!--end::Container-->
        </div>
    </main> <!--end::App Main--> <!--begin::Footer-->
@endsection
