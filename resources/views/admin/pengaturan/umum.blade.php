@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')
        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row g-4"> <!--begin::Col-->
                    <div class="col-md-12 col-lg-6 col-sm-12"> <!--begin::Quick Example-->
                        <div class="card card-primary card-outline mb-4"> <!--begin::Header-->
                            <div class="card-header">
                                <div class="card-title">Form Setting Aplikasi</div>
                            </div> <!--end::Header--> <!--begin::Form-->
                            <form action="{{ route('setting.update') }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nama Aplikasi</label>
                                        <input type="text" name="nama_aplikasi" id="nama_aplikasi"
                                            class="form-control @error('nama_aplikasi') is-invalid @enderror"
                                            value="{{ @old('nama_aplikasi', $nama_aplikasi->value) }}">
                                        @error('nama_aplikasi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Nomor WhatsApp Aplikasi</label>
                                        <input type="text" name="nowa" id="nowa"
                                            class="form-control @error('nowa') is-invalid @enderror"
                                            value="{{ @old('nowa', $nowa->value) }}">
                                        @error('nowa')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Token Fonnte</label>
                                        <div class="input-group">
                                            <input type="password" name="token" id="token"
                                                class="form-control @error('token') is-invalid @enderror"
                                                value="{{ @old('token', $token->value) }}">
                                            <span class="input-group-text">
                                                <a href="javascript:void(0);"><i class="bi bi-eye-slash"
                                                        id="toggleToken"></i></a>
                                            </span>
                                        </div>
                                        @error('token')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="form-group mb-3">
                                        <label class="form-label">Format Pesan Tagihan</label>
                                        <a href="javascript:void(0);" class="text-danger" data-bs-toggle="tooltip"
                                            data-bs-placement="top" data-bs-custom-class="custom-tooltip"
                                            data-bs-title="Gunakan {name} = Nama Penyewa, {var1} = Nominal Tagihan, {var2} = Nama Kamar">
                                            <i class="bi bi-info-circle-fill"></i>
                                        </a>
                                        <textarea class="form-control @error('format_tagihan') is-invalid @enderror" name="format_tagihan" id="format_tagihan"
                                            cols="30" rows="5">{{ @old('format_tagihan', $format_tagihan->value) }}</textarea>
                                        @error('format_tagihan')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <a href="{{ route('setting.test') }}"><i class="bi bi-send"></i> Testing Kirim</a>
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
@push('custom-js')
    <script type="text/javascript">
        const toggleToken = document.getElementById('toggleToken');
        const password = document.getElementById('token');

        toggleToken.addEventListener('click', () => {
            // Toggle the type attribute using getAttribute() method
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle the eye and eye-slash icon classes
            toggleToken.classList.toggle('bi-eye');
            toggleToken.classList.toggle('bi-eye-slash');
        });

        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
@endpush
