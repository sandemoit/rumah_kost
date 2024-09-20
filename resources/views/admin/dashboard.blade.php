@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')

        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row"> <!--begin::Col-->
                    @foreach ($kontrakan as $index => $data)
                        <div class="col-lg-3 col-6"> <!--begin::Small Box Widget 1-->
                            @php
                                $colors = [
                                    'text-bg-primary',
                                    'text-bg-success',
                                    'text-bg-warning',
                                    'text-bg-danger',
                                    'text-bg-info',
                                    'text-bg-secondary',
                                    'text-bg-white',
                                ];
                            @endphp
                            <div class="small-box {{ $colors[$index % count($colors)] }}">
                                <div class="inner">
                                    <h3>{{ $data['nama_kontrakan'] }}</h3>
                                    <p>{{ $data['totalKamar'] }} Pintu</p>
                                    <p>{{ $data['terisi'] }} Terisi</p>
                                    <p>{{ $data['kosong'] }} Kosong</p>
                                    <p>{{ $data['nunggak'] }} Nunggak</p>
                                </div>
                                <svg class="small-box-icon" fill="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                    <path
                                        d="M3 13h1v7c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-7h1a1 1 0 0 0 .707-1.707l-9-9a.999.999 0 0 0-1.414 0l-9 9A1 1 0 0 0 3 13zm7 7v-5h4v5h-4zm2-15.586 6 6V15l.001 5H16v-5c0-1.103-.897-2-2-2h-4c-1.103 0-2 .897-2 2v5H6v-9.586l6-6z">
                                    </path>
                                </svg>
                            </div> <!--end::Small Box Widget 1-->
                        </div> <!--end::Col-->
                    @endforeach
                </div> <!--end::Row--> <!--begin::Row-->
            </div> <!--end::Container-->
        </div> <!--end::App Content-->
    </main> <!--end::App Main--> <!--begin::Footer-->
@endsection
