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
                                                <div class="delete_button" alt="delete" title="Hapus kategori"
                                                    data-id="{{ $income->id }}">
                                                    <i class="bi bi-x-circle"></i>
                                                </div> &nbsp;
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
                                                @method('PUT')
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
                                                        <div class="loading" id="loading_{{ $income->id }}"
                                                            style="display: none;">Loading...</div>
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
                                        <span class="katname kapital"
                                            id="katname_{{ $outcome->id }}">{{ $outcome->category_name }}</span>
                                        <div class="katedit" id="katedit_{{ $outcome->id }}" style="display: block;">
                                            <div class="btn-group">
                                                <div class="delete_button" alt="delete" title="Hapus kategori"
                                                    data-id="{{ $outcome->id }}">
                                                    <i class="bi bi-x-circle"></i>
                                                </div> &nbsp;
                                                <div class="edit_button" alt="edit" title="Edit kategori"
                                                    data-id="{{ $outcome->id }}">
                                                    <i class="bi bi-pencil-square"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
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
                    const submitButton = document.querySelector(`.submit_button[data-id="${id}"]`);
                    const cancelButton = document.querySelector(`.cancel_button[data-id="${id}"]`);
                    const loadingDiv = document.getElementById(`loading_${id}`);

                    // Sembunyikan tombol Simpan dan tampilkan animasi loading
                    submitButton.style.display = 'none';
                    cancelButton.style.display = 'none';
                    loadingDiv.style.display = 'block';

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content'),
                                'X-HTTP-Method-Override': 'PUT',
                            },
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Sembunyikan animasi loading
                            loadingDiv.style.display = 'none';

                            if (data.success) {
                                // Refresh halaman setelah sukses
                                location.reload();
                            } else {
                                alert('Terjadi kesalahan: ' + data.message);
                                // Tampilkan kembali tombol Simpan setelah selesai
                                submitButton.style.display = 'flex';
                                cancelButton.style.display = 'flex';
                            }
                        })
                        .catch(error => {
                            loadingDiv.style.display = 'none';
                            alert('Terjadi kesalahan saat mengirim data.');
                            console.error('Error:', error);
                            // Tampilkan kembali tombol Simpan setelah selesai
                            submitButton.style.display = 'flex';
                            cancelButton.style.display = 'flex';
                        });
                });
            });
        });
    </script>
@endpush
