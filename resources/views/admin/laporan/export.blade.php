@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')

        <div class="app-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-3 col-md-6 col-sm-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Card title</h5>
                                <p class="card-text">Some quick example text to build on the card title and make up the bulk
                                    of the
                                    card's content.</p>
                                <a href="#" id="export" title="Export to Excel" class="btn btn-success"><i
                                        class="bi bi-file-earmark-arrow-down"></i>
                                    Download Excel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> <!--end::App Content-->
    </main>
@endsection
