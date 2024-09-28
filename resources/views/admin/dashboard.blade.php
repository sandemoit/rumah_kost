@push('load-css')
    <style>
        .kontrakan-card {
            position: relative;
            background-color: white;
            border-radius: 23px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            /* Lighter shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .kontrakan-card:hover {
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
            /* Darker shadow on hover */
            transform: translateY(-5px);
            /* Slight lift on hover */
        }

        @media (max-width: 767px) {
            .kontrakan-card {
                margin: 15px:
            }
        }

        @media (min-width: 768px) and (max-width: 991px) {
            .kontrakan-card {
                margin: 15px:
            }
        }

        .corner-accent {
            height: 128px;
            width: 128px;
            z-index: 1;
            position: absolute;
            top: -75px;
            right: -75px;
            border-radius: 50%;
            -webkit-transition: all .5s ease;
            -o-transition: all .5s ease;
            transition: all .5s ease;
        }

        .inner:hover,
        .inner:hover {
            text-decoration: none;
            color: #FFF;
        }

        .inner:hover .corner-accent {
            -webkit-transform: scale(10);
            -ms-transform: scale(10);
            transform: scale(10);
        }

        .kontrakan-info {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .kontrakan-left,
        .kontrakan-right {
            width: 48%;
        }

        .kontrakan-details {
            font-size: 1.1rem;
            margin: 5px 0;
            overflow: hidden;
            position: relative;
            z-index: 2;
        }

        .kontrakan-heading {
            overflow: hidden;
            position: relative;
            z-index: 2;
            font-weight: 600
        }

        .kontrakan-card {
            position: relative;
            overflow: hidden;
        }

        .inner {
            padding: 30px 20px;
        }
    </style>
@endpush
@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')

        <div class="app-content"> <!--begin::Container-->
            <div class="container-fluid"> <!--begin::Row-->
                <div class="row">
                    @foreach ($kontrakan as $index => $data)
                        <div class="col-lg-3 col-sm-12 col-md-6">
                            @php
                                $colors = [
                                    'bg-primary',
                                    'bg-success',
                                    'bg-warning',
                                    'bg-danger',
                                    'bg-info',
                                    'bg-secondary',
                                    'bg-light',
                                ];
                            @endphp
                            <div class="kontrakan-card">
                                <div class="inner">
                                    <div class="corner-accent {{ $colors[$index % count($colors)] }}"></div>
                                    <h3 class="kontrakan-heading"><i class="bi bi-door-closed"></i>
                                        {{ $data['nama_kontrakan'] }}</h3>
                                    <div class="kontrakan-info">
                                        <div class="kontrakan-left">
                                            <p class="kontrakan-details">{{ $data['totalKamar'] }} Pintu</p>
                                            <p class="kontrakan-details">{{ $data['kosong'] }} Kosong</p>
                                        </div>
                                        <div class="kontrakan-right">
                                            <p class="kontrakan-details">{{ $data['terisi'] }} Terisi</p>
                                            <p class="kontrakan-details">{{ $data['nunggak'] }} Nunggak</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div> <!--end::Row--> <!--begin::Row-->
            </div> <!--end::Container-->
        </div> <!--end::App Content-->
    </main> <!--end::App Main--> <!--begin::Footer-->
@endsection
