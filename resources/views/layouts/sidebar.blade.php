<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark"> <!--begin::Sidebar Brand-->
    <div class="sidebar-brand"> <!--begin::Brand Link--> <a href="{{ route('dashboard') }}" class="brand-link">
            <!--begin::Brand Image--> <img src="{{ asset('assets/logo/logo.jpg') }}" alt="{{ config('app.name') }}"
                class="brand-image opacity-75 shadow"> <!--end::Brand Image-->
            <!--begin::Brand Text--> <span class="brand-text fw-light">{{ config('app.name') }}</span>
            <!--end::Brand Text-->
        </a>
        <!--end::Brand Link-->
    </div> <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
    @if (Auth::user()->role == 'admin')
        <div class="sidebar-wrapper">
            <nav class="mt-2"> <!--begin::Sidebar Menu-->
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
                    data-accordion="false">
                    <li class="nav-item"> <a href="{{ route('dashboard') }}"
                            class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'dashboard' ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    {{-- buku kas --}}
                    <li class="nav-item {{ request()->segment(1) == 'transaksi' ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->segment(1) == 'transaksi' ? 'active' : '' }}">
                            <i class="nav-icon bi bi-journal-text"></i>
                            <p>
                                Buku KAS
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (getAllKontrakan() as $kontrakan)
                                <li class="nav-item">
                                    <a href="{{ route('transaksi.kontrakan', $kontrakan->code_kontrakan) }}"
                                        class="nav-link {{ request()->segment(2) == $kontrakan->code_kontrakan ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle-fill"></i>
                                        <p>{{ $kontrakan->nama_kontrakan }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>

                    {{-- laporan --}}
                    <li class="nav-item {{ request()->segment(1) == 'laporan' ? 'menu-open' : '' }}">
                        <a href="javascript:void(0)"
                            class="nav-link {{ request()->segment(1) == 'laporan' ? 'active' : '' }}">
                            <i class="nav-icon bi bi-file-earmark-bar-graph"></i>
                            <p>
                                Laporan
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('laporan.harian') }}"
                                    class="nav-link {{ request()->segment(2) == 'harian' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>Harian</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('laporan.bulanan') }}"
                                    class="nav-link {{ request()->segment(2) == 'bulanan' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>Bulanan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('laporan.tahunan') }}"
                                    class="nav-link {{ request()->segment(2) == 'tahunan' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>Tahunan</p>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('laporan.custom') }}"
                                    class="nav-link {{ request()->segment(2) == 'custom' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>Custom</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    {{-- pengaturan --}}
                    <li class="nav-item @if (in_array(request()->segment(1), ['penyewa', 'kontrakan', 'usermanajemen'])) menu-open @endif">
                        <a href="#" class="nav-link @if (in_array(request()->segment(1), ['penyewa', 'kontrakan', 'usermanajemen'])) active @endif">
                            <i class="nav-icon bi bi-gear"></i>
                            <p>
                                Pengaturan
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"> <a href="{{ route('kontrakan') }}"
                                    class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'kontrakan' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>Kontrakan</p>
                                </a>
                            </li>
                            <li class="nav-item"> <a href="{{ route('penyewa') }}"
                                    class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'penyewa' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>Penyewa</p>
                                </a>
                            </li>
                            {{-- <li class="nav-item"> <a href="{{ route('cashcategory') }}"
                                class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'cashcategory' ? 'active' : '' }}">
                                <i class="nav-icon bi-card-list"></i>
                                <p>Kategori</p>
                            </a>
                        </li> --}}
                            <li class="nav-item"> <a href="{{ route('usermanajemen') }}"
                                    class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'usermanajemen' ? 'active' : '' }}">
                                    <i class="nav-icon bi bi-circle-fill"></i>
                                    <p>User Manajemen</p>
                                </a>
                            </li>
                        </ul> <!--end::Sidebar Menu-->
                    </li>
                </ul> <!--end::Sidebar Menu-->
            </nav>
        </div> <!--end::Sidebar Wrapper-->
    @else
        <div class="sidebar-wrapper">
            <nav class="mt-2"> <!--begin::Sidebar Menu-->
                <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu"
                    data-accordion="false">
                    <li class="nav-item"> <a href="{{ route('dashboard') }}"
                            class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'dashboard' ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>
                    <li class="nav-item"> <a href="{{ route('penyewa') }}"
                            class="nav-link {{ request()->segment(1) == '' || request()->segment(1) == 'penyewa' ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Penyewa</p>
                        </a>
                    </li>
                    {{-- buku kas --}}
                    <li class="nav-item {{ request()->segment(1) == 'transaksi' ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->segment(1) == 'transaksi' ? 'active' : '' }}">
                            <i class="nav-icon bi bi-journal-text"></i>
                            <p>
                                Buku KAS
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            @foreach (getAllKontrakan() as $kontrakan)
                                <li class="nav-item">
                                    <a href="{{ route('transaksi.kontrakan', $kontrakan->code_kontrakan) }}"
                                        class="nav-link {{ request()->segment(2) == $kontrakan->code_kontrakan ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle-fill"></i>
                                        <p>{{ $kontrakan->nama_kontrakan }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                </ul> <!--end::Sidebar Menu-->
            </nav>
        </div> <!--end::Sidebar Wrapper-->
    @endif
</aside>
