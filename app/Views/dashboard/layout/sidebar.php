<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('dashboard') ?>">
                    <i class="mdi mdi-grid-large menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="<?= site_url('dashboard/scan-whatsapp') ?>">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Scan Whatsapp</span>
                </a>
            </li>
            <li class="nav-item nav-category">NLP</li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#ui-basic" aria-expanded="false"
                    aria-controls="ui-basic">
                    <i class="menu-icon mdi mdi-floor-plan"></i>
                    <span class="menu-title">NLP</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="ui-basic">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"> <a class="nav-link" href="pages/ui-features/buttons.html">Document</a>
                        </li>
                        <li class="nav-item"> <a class="nav-link" href="pages/ui-features/dropdowns.html">Answer</a>
                        <li class="nav-item"> <a class="nav-link" href="pages/ui-features/dropdowns.html">Normalize
                                Text</a>
                        </li>
                        <li class="nav-item"> <a class="nav-link"
                                href="pages/ui-features/typography.html">Typography</a></li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false"
                    aria-controls="form-elements">
                    <i class="menu-icon mdi mdi-card-text-outline"></i>
                    <span class="menu-title">Chat</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse" id="form-elements">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/history-chat') ?>">History
                                Chat</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/support-chat') ?>">Answer
                                Chat</a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </nav>
