<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="#">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Quản lý tour
    </div>

    <!-- Nav Item - Pages partner Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-plane"></i>
            <span>Tours</span>
        </a>
        <div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Chức năng tour:</h6>
                <a class="collapse-item" href="{{ route('admin.mana-tour.index') }}">Danh sách tours</a>
                <a class="collapse-item" href="{{ route('admin.mana-tour.create') }}">Thêm tour</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Service Menu -->
    <!-- Nav Item - Booking Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseUtilities"
            aria-expanded="true" aria-controls="collapseUtilities">
            <i class="fas fa-ticket-alt"></i>
            <span>Đặt tour & khách</span>
        </a>
        <div id="collapseUtilities" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Quản lý đặt tour:</h6>
                <a class="collapse-item" href="{{ route('admin.mana-booking.index') }}">Đơn đặt tour</a>
                <a class="collapse-item" href="{{ route('admin.customer-tour.index') }}">Khách theo tour</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Product Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseThree"
            aria-expanded="true" aria-controls="collapseThree">
            <i class="fab fa-product-hunt"></i>
            <span>Hướng dẫn viên</span>
        </a>
        <div id="collapseThree" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Chức năng hướng dẫn viên:</h6>
                <a class="collapse-item" href="{{ route('admin.mana-guide.index') }}">Danh sách HDV</a>
            </div>
        </div>
    </li>

    <!-- Heading -->
    <div class="sidebar-heading">
        Nhân viên
    </div>
    <!-- Nav Item - Customer Support Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFour"
            aria-expanded="true" aria-controls="collapseFour">
            <i class="fas fa-user-cog"></i>
            <span>Hỗ trợ đặt tour</span>
        </a>
        <div id="collapseFour" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Chức năng nhân viên:</h6>
                <a class="collapse-item" href="{{ route('admin.staff-booking.tours') }}">Danh sách tour khách đặt</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Hiring Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseFive"
            aria-expanded="true" aria-controls="collapseFive">
            <i class="fas fa-user-friends"></i>
            <span>Tuyển dụng</span>
        </a>
        <div id="collapseFive" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Chức năng tuyển dụng:</h6>
                <a class="collapse-item" href="#">Danh sách tuyển dụng</a>
                <a class="collapse-item" href="#">Thêm tuyển dụng</a>
                <a class="collapse-item" href="#">Danh sách ứng viên</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Contact Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSix"
            aria-expanded="true" aria-controls="collapseSix">
            <i class="fas fa-id-card"></i>
            <span>Liên lạc</span>
        </a>
        <div id="collapseSix" class="collapse" aria-labelledby="headingUtilities" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Chức năng liên lạc:</h6>
                <a class="collapse-item" href="#">Danh sách liên lạc</a>
            </div>
        </div>
    </li>

    {{-- <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Addons
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapsePages"
            aria-expanded="true" aria-controls="collapsePages">
            <i class="fas fa-fw fa-folder"></i>
            <span>Pages</span>
        </a>
        <div id="collapsePages" class="collapse" aria-labelledby="headingPages" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Login Screens:</h6>
                <a class="collapse-item" href="login.html">Login</a>
                <a class="collapse-item" href="register.html">Register</a>
                <a class="collapse-item" href="forgot-password.html">Forgot Password</a>
                <div class="collapse-divider"></div>
                <h6 class="collapse-header">Other Pages:</h6>
                <a class="collapse-item" href="404.html">404 Page</a>
                <a class="collapse-item" href="blank.html">Blank Page</a>
            </div>
        </div>
    </li>

    <!-- Nav Item - Charts -->
    <li class="nav-item">
        <a class="nav-link" href="charts.html">
            <i class="fas fa-fw fa-chart-area"></i>
            <span>Charts</span></a>
    </li>

    <!-- Nav Item - Tables -->
    <li class="nav-item">
        <a class="nav-link" href="tables.html">
            <i class="fas fa-fw fa-table"></i>
            <span>Tables</span></a>
    </li> --}}

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

    <!-- Sidebar Message -->
    {{-- <div class="sidebar-card d-none d-lg-flex">
                <img class="sidebar-card-illustration mb-2" src="img/undraw_rocket.svg" alt="...">
                <p class="text-center mb-2"><strong>SB Admin Pro</strong> is packed with premium features, components,
                    and more!</p>
                <a class="btn btn-success btn-sm" href="https://startbootstrap.com/theme/sb-admin-pro">Upgrade to
                    Pro!</a>
            </div> --}}

</ul>
<!-- End of Sidebar -->
