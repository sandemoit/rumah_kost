@extends('layouts.app')
@section('content')
    <main class="app-main"> <!--begin::App Content Header-->
        @include('layouts.heading')
        <div class="app-content">
            <div class="container-fluid">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card card-primary card-outline mb-4"> <!--begin::Header-->
                            <div class="card-header">
                                <div class="card-title">Pemasukan</div>
                            </div>
                            <div class="card-body">
                                @foreach ($pemasukan as $income)
                                    <div class="katnamebox">
                                        <span class="katname kapital" id="katname_{{ $income->id }}"
                                            style="display: inline-block;">{{ $income->category_name }}</span>
                                        <div class="katedit" id="katedit_{{ $income->id }}" style="display: block;">
                                            <div class="btn-group">
                                                <a href="{{ route('cashcategory.destroy', $income->id) }}"
                                                    class="delete_button" alt="delete" title="Hapus kategori"
                                                    data-id="{{ $income->id }}" onclick="confirmDelete(event, this)">
                                                    <i class="bi bi-x-circle"></i>
                                                </a> &nbsp;
                                                <div class="edit_button" alt="edit" title="Edit kategori"
                                                    data-id="{{ $income->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Formulir Edit -->
                                        <div class="form-edit" id="form_edit_{{ $income->id }}" style="display: none;">
                                            <form id="edit_form_{{ $income->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-grow-1">
                                                        <input type="text" name="category_name"
                                                            id="category_name_{{ $income->id }}" class="form-control"
                                                            value="{{ $income->category_name }}">
                                                    </div>
                                                    <div class="ms-2">
                                                        <button type="button" class="btn btn-secondary cancel_button"
                                                            data-id="{{ $income->id }}">Batal</button>
                                                        <button type="submit" class="btn btn-primary submit_button"
                                                            data-id="{{ $income->id }}">Simpan</button>
                                                        <div class="spinner-border text-primary" role="status"
                                                            id="loading_{{ $income->id }}" style="display: none;"> <span
                                                                class="visually-hidden">Loading...</span> </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                <form action="{{ route('cashcategory.store') }}" method="POST">
                                    @csrf
                                    <div class="mt-4 mb-4 d-flex align-items-end">
                                        <div class="flex-grow-1">
                                            <label for="category_pemasukan" class="form-label">Buat kategori
                                                Pemasukan</label>
                                            <input type="text" class="form-control " id="category_pemasukan"
                                                placeholder="Ketik Kategori pemasukan" name="category_pemasukan">
                                            <div id="validationCategory" class="invalid-feedback">
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <button type="submit" class="btn btn-primary">Tambah</button>
                                        </div>
                                    </div>
                                </form>
                            </div> <!--end::Body-->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-primary card-outline mb-4"> <!--begin::Header-->
                            <div class="card-header">
                                <div class="card-title">Pengeluaran</div>
                            </div>
                            <div class="card-body">
                                @foreach ($pengeluaran as $outcome)
                                    <div class="katnamebox">
                                        <span class="katname kapital" id="katname_{{ $outcome->id }}"
                                            style="display: inline-block;">{{ $outcome->category_name }}</span>
                                        <div class="katedit" id="katedit_{{ $outcome->id }}" style="display: block;">
                                            <div class="btn-group">
                                                <a href="{{ route('cashcategory.destroy', $outcome->id) }}"
                                                    class="delete_button" alt="delete" title="Hapus kategori"
                                                    data-id="{{ $outcome->id }}" onclick="confirmDelete(event, this)">
                                                    <i class="bi bi-x-circle"></i>
                                                </a> &nbsp;
                                                <div class="edit_button" alt="edit" title="Edit kategori"
                                                    data-id="{{ $outcome->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Formulir Edit -->
                                        <div class="form-edit" id="form_edit_{{ $outcome->id }}" style="display: none;">
                                            <form id="edit_form_{{ $outcome->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="d-flex align-items-end">
                                                    <div class="flex-grow-1">
                                                        <input type="text" name="category_name"
                                                            id="category_name_{{ $outcome->id }}" class="form-control"
                                                            value="{{ $outcome->category_name }}">
                                                    </div>
                                                    <div class="ms-2">
                                                        <button type="button" class="btn btn-secondary cancel_button"
                                                            data-id="{{ $outcome->id }}">Batal</button>
                                                        <button type="submit" class="btn btn-primary submit_button"
                                                            data-id="{{ $outcome->id }}">Simpan</button>
                                                        <div class="spinner-border text-primary" role="status"
                                                            id="loading_{{ $outcome->id }}" style="display: none;">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                                <form action="{{ route('cashcategory.store') }}" method="POST">
                                    @csrf
                                    <div class="mt-4 mb-4 d-flex align-items-end">
                                        <div class="flex-grow-1">
                                            <label for="category_pengeluaran" class="form-label">Buat kategori
                                                Pengeluaran</label>
                                            <input type="text" class="form-control " id="category_pengeluaran"
                                                placeholder="Ketik Kategori pemasukan" name="category_pengeluaran">
                                            <div id="validationCategory" class="invalid-feedback">
                                            </div>
                                        </div>
                                        <div class="ms-2">
                                            <button type="submit" class="btn btn-primary">Tambah</button>
                                        </div>
                                    </div>
                                </form>
                            </div> <!--end::Body-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main> <!--end::App Main--> <!--begin::Footer-->
@endsection
@push('custom-js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.edit_button').forEach(function(button) {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const formEdit = document.getElementById(`form_edit_${id}`);
                    const katname = document.getElementById(`katname_${id}`);
                    const katedit = document.getElementById(`katedit_${id}`);

                    formEdit.style.display = 'block';
                    katname.style.display = 'none';
                    katedit.style.display = 'none';
                });
            });

            document.querySelectorAll('.cancel_button').forEach(function(button) {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const formEdit = document.getElementById(`form_edit_${id}`);
                    const katname = document.getElementById(`katname_${id}`);
                    const katedit = document.getElementById(`katedit_${id}`);

                    formEdit.style.display = 'none';
                    katname.style.display = 'inline-block';
                    katedit.style.display = 'block';
                });
            });

            document.querySelectorAll('form[id^="edit_form_"]').forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    event.preventDefault();

                    const id = this.id.replace('edit_form_', '');
                    const url = `/cashcategory/${id}`;
                    const formData = new FormData(this);
                    const katname = document.getElementById(`katname_${id}`);
                    const katedit = document.getElementById(`katedit_${id}`);
                    const formEdit = document.getElementById(`form_edit_${id}`);
                    const submitButton = document.querySelector(`.submit_button[data-id="${id}"]`);
                    const cancelButton = document.querySelector(`.cancel_button[data-id="${id}"]`);
                    const loadingDiv = document.getElementById(`loading_${id}`);

                    // Sembunyikan tombol Simpan dan Batal, tampilkan animasi loading
                    submitButton.style.display = 'none';
                    cancelButton.style.display = 'none';
                    loadingDiv.style.display = 'block';

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'X-HTTP-Method-Override': 'PATCH',
                            },
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Sembunyikan animasi loading
                            loadingDiv.style.display = 'none';

                            if (data.success) {
                                showSuccessToast(data.message);

                                // Refresh bagian yang diperlukan tanpa reload seluruh halaman
                                formEdit.style.display = 'none';
                                katname.textContent = formData.get('category_name');
                                katname.style.display = 'inline-block';
                                submitButton.style.display = 'inline-block';
                                cancelButton.style.display = 'inline-block';
                                katedit.style.display = 'block';
                            } else {
                                showErrorToast(data.message);
                                // Tampilkan kembali tombol Simpan dan Batal setelah selesai
                                submitButton.style.display = 'inline-block';
                                cancelButton.style.display = 'inline-block';
                            }
                        })
                        .catch(error => {
                            loadingDiv.style.display = 'none';
                            showErrorToast(data.message);
                            console.error('Error:', error);
                            // Tampilkan kembali tombol Simpan dan Batal setelah selesai
                            submitButton.style.display = 'inline-block';
                            cancelButton.style.display = 'inline-block';
                        });
                });
            });

            function showSuccessToast(message) {
                Swal.fire({
                    icon: 'success',
                    title: message,
                    toast: true,
                    iconColor: 'white',
                    position: 'top-end',
                    customClass: {
                        popup: 'colored-toast',
                    },
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            }

            function showErrorToast(message) {
                Swal.fire({
                    icon: 'error',
                    title: message,
                    toast: true,
                    iconColor: 'white',
                    position: 'top-end',
                    customClass: {
                        popup: 'colored-toast',
                    },
                    timer: 3000,
                    showConfirmButton: false,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer)
                        toast.addEventListener('mouseleave', Swal.resumeTimer)
                    }
                });
            }
        });
    </script>
@endpush
