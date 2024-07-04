<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="login-box">
        <div class="login-logo"> <a href="#"><b>CATAT</b>.BIZ</a> </div> <!-- /.login-logo -->
        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">Masukkan email dan kata sandi Anda untuk masuk</p>
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="input-group mb-3"> <input id="email" class="form-control" type="email"
                            name="email" :value="old('email')" required autofocus autocomplete="email"
                            placeholder="Masukan email Anda">
                        <div class="input-group-text"> <span class="bi bi-envelope"></span> </div>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}</div>
                    @enderror

                    <div class="input-group mb-3"> <input id="password" class="form-control" type="password"
                            name="password" required autocomplete="password" placeholder="Masukan password Anda">
                        <div class="input-group-text"> <span class="bi bi-lock-fill"></span> </div>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">
                            {{ $message }}</div>
                    @enderror
                    <!--begin::Row-->

                    <div class="row">
                        <div class="col-8">
                            <div class="form-check"> <input class="form-check-input" type="checkbox" name="remember"
                                    id="flexCheckDefault"> <label class="form-check-label" for="flexCheckDefault">
                                    Ingat Saya
                                </label> </div>
                        </div> <!-- /.col -->
                        <div class="col-4">
                            <div class="d-grid gap-2">
                                <button class="btn btn-primary" type="submit">
                                    {{ __('Masuk') }}
                                </button>
                            </div>
                        </div> <!-- /.col -->
                    </div> <!--end::Row--> <!-- /.social-auth-links -->
                </form>
                {{-- <p class="mb-1"> <a href="forgot-password.html">I forgot my password</a> </p>
                <p class="mb-0"> <a href="register.html" class="text-center">
                        Register a new membership
                    </a> </p> --}}
            </div> <!-- /.login-card-body -->
        </div>
    </div>

</x-guest-layout>
