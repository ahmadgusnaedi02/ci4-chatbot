<?php $adminAvatar = session('admin_avatar') ?: 'assets/images/faces/face8.jpg'; ?>
<body class="with-welcome-text admin-dashboard">
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        <nav class="navbar default-layout col-lg-12 col-12 p-0 fixed-top d-flex align-items-top flex-row">
            <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
                <div class="admin-brand">
                    <button class="navbar-toggler admin-sidebar-logo-toggle" type="button" data-bs-toggle="minimize"
                        aria-label="Toggle sidebar">
                        <img src="<?= base_url('assets/images/logo-yapas.png') ?>" alt="Logo Yapas" />
                    </button>
                    <a class="admin-brand-text" href="<?= site_url('dashboard') ?>">Admin SPMB</a>
                </div>
            </div>
            <div class="navbar-menu-wrapper d-flex align-items-top">
                <div class="navbar-welcome d-none d-md-block">
                    <h5 class="mb-1">Dashboard Sekolah</h5>
                    <p class="mb-0">Kelola chatbot, WhatsApp, dan percakapan SPMB.</p>
                </div>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown d-none d-lg-block user-dropdown">
                        <a class="nav-link" id="UserDropdown" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <img class="img-xs rounded-circle admin-navbar-avatar" src="<?= base_url($adminAvatar) ?>" alt="Profile image">
                        </a>
                        <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="UserDropdown">
                            <div class="dropdown-header text-center">
                                <img class="img-md rounded-circle admin-dropdown-avatar" src="<?= base_url($adminAvatar) ?>"
                                    alt="Profile image">
                                <p class="mb-1 mt-3 fw-semibold"><?= esc(session('admin_name') ?? 'Admin Sekolah') ?></p>
                                <p class="fw-light text-muted mb-0"><?= esc(session('admin_email') ?? 'admin@sekolah.test') ?></p>
                            </div>

                            <a class="dropdown-item" href="<?= site_url('dashboard/profile') ?>"><i
                                    class="dropdown-item-icon mdi mdi-account-cog text-primary me-2"></i>Setting Profil</a>
                            <a class="dropdown-item" href="<?= site_url('admin/logout') ?>"><i
                                    class="dropdown-item-icon mdi mdi-power text-primary me-2"></i>Logout</a>
                        </div>
                    </li>
                </ul>
                <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
                    data-bs-toggle="offcanvas">
                    <span class="mdi mdi-menu"></span>
                </button>
            </div>
        </nav>
