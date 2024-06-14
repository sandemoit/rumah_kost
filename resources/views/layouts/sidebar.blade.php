<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"> <!--begin::Sidebar Brand-->
    <div class="sidebar-brand"> <!--begin::Brand Link--> <a href="./index.html" class="brand-link">
            <!--begin::Brand Image--> <img src="{{ url('assets') }}/assets/img/AdminLTELogo.png" alt="AdminLTE Logo"
                class="brand-image opacity-75 shadow"> <!--end::Brand Image-->
            <!--begin::Brand Text--> <span class="brand-text fw-light">AdminLTE 4</span> <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div> <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2"> <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">
                <li class="nav-item"> <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'dashboard' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-header">Data Master</li>
                <li class="nav-item"> <a href="{{ route('cashcategory') }}"
                        class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'cashcategory' ? 'active' : '' }}">
                        <i class="nav-icon bi-card-list"></i>
                        <p>Kategori</p>
                    </a>
                </li>
                <li class="nav-item"> <a href="{{ route('usermanajemen') }}"
                        class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'usermanajemen' ? 'active' : '' }}">
                        <i class="nav-icon bi bi-people"></i>
                        <p>User Manajemen</p>
                    </a>
                </li>
                <li class="nav-item"> <a href="#" class="nav-link"> <i
                            class="nav-icon bi bi-box-arrow-in-right"></i>
                        <p>
                            Auth
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"> <a href="#" class="nav-link"> <i
                                    class="nav-icon bi bi-box-arrow-in-right"></i>
                                <p>
                                    Version 1
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"> <a href="./examples/login.html" class="nav-link"> <i
                                            class="nav-icon bi bi-circle"></i>
                                        <p>Login</p>
                                    </a> </li>
                                <li class="nav-item"> <a href="./examples/register.html" class="nav-link"> <i
                                            class="nav-icon bi bi-circle"></i>
                                        <p>Register</p>
                                    </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item"> <a href="#" class="nav-link"> <i
                                    class="nav-icon bi bi-box-arrow-in-right"></i>
                                <p>
                                    Version 2
                                    <i class="nav-arrow bi bi-chevron-right"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item"> <a href="./examples/login-v2.html" class="nav-link"> <i
                                            class="nav-icon bi bi-circle"></i>
                                        <p>Login</p>
                                    </a> </li>
                                <li class="nav-item"> <a href="./examples/register-v2.html" class="nav-link">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Register</p>
                                    </a> </li>
                            </ul>
                        </li>
                        <li class="nav-item"> <a href="./examples/lockscreen.html" class="nav-link"> <i
                                    class="nav-icon bi bi-circle"></i>
                                <p>Lockscreen</p>
                            </a> </li>
                    </ul>
                </li>
            </ul> <!--end::Sidebar Menu-->
        </nav>
    </div> <!--end::Sidebar Wrapper-->
</aside>
