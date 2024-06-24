@push('load-css')
    <link rel="stylesheet" href="{{ asset('assets/css/transaksi.css') }}">
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        <div class="app-content-header"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row">
                    <div class="col-sm-6">
                        <h3 class="mb-0">{{ __($pageTitle) }}</h3>
                        <p>{{ __($keterangan) }}</p>
                    </div>
                    <div class="col-sm-6 text-end">
                        <strong>Saldo</strong>
                        <h3 class="mb-0">Rp 7.500.000,00</h3>
                        <span class="smallsaldo">Semua Buku Kas: Rp 10.350.000,00</span>
                    </div>
                </div> <!--end::Row-->
            </div> <!--end::Container-->
        </div>

    </main>
@endsection
