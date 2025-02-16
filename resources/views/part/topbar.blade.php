@push('styles')
    <style>
        /* Style untuk sidebar ketika toggled */
        .sidebar.toggled {
            width: 0 !important;
            overflow: hidden;
        }

        /* Style untuk body ketika sidebar toggled */
        body.sidebar-toggled .sidebar {
            width: 0 !important;
        }
    </style>
@endpush



@push('scripts')
    <!-- Pastikan jQuery sudah dimuat -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <!-- Pastikan Bootstrap JS sudah dimuat -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Page level custom scripts -->
    <script>
        $(document).ready(function() {
            $('#sidebarToggleTop').on('click', function(e) {
                e.preventDefault();
                $('body').toggleClass('sidebar-toggled');
                $('.sidebar').toggleClass('toggled');
                
                // Tambahkan console.log untuk debugging
                console.log('Sidebar toggle clicked');
                console.log('Body classes:', $('body').attr('class'));
                console.log('Sidebar classes:', $('.sidebar').attr('class'));
            });
        });
    </script>
@endpush

<nav class="navbar navbar-expand navbar-light bg-navbar topbar mb-4 static-top">
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    <ul class="navbar-nav ml-auto">

        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                @if ($profile->photoProfile != null)
                    <img class="img-profile rounded-circle"
                        src="{{ asset('/images/photoProfile/' . $profile->photoProfile) }}" style="max-width: 60px">
                @else
                    <img class="img-profile rounded-circle" src="{{ asset('template/img/boy.png') }}"
                        style="max-width: 60px">
                @endif
                <span class="ml-2 d-none d-lg-inline text-white small">{{ Auth::user()->name }}</span>
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="/profile">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ route('logout') }}" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>
    </ul>
</nav>
