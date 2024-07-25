<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Ganti Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak agar tetap aman.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div class="form-group mb-3">
            <label for="update_password_current_password" class="form-label">Current Password</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control"
                autocomplete="current-password" />
            @error('current_password')
                <div class="error invalid-feedback">
                    {{ $errors }}</div>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label for="update_password_password" class="form-label">New Password</label>
            <input id="update_password_password" name="password" type="password" class="form-control"
                autocomplete="new-password" />
            @error('password')
                <div class="error invalid-feedback">
                    {{ $errors }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="update_password_password_confirmation" class="form-label">New Password</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="form-control" autocomplete="new-password" />
            @error('password_confirmation')
                <div class="error invalid-feedback">
                    {{ $errors }}</div>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-3">
            <button type="submit" class="btn btn-primary">{{ __('Simpan') }}</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition.duration.1500ms x-init="setTimeout(() => show = false, 2000)"
                    class="text-success">{{ __('Berhasil disimpan.') }}</p>
            @endif
        </div>
    </form>
</section>
