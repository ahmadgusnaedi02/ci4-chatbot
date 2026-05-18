<div class="container-fluid page-body-wrapper">
    <!-- partial:partials/_sidebar.html -->
    <?php $currentPath = service('uri')->getPath(); ?>
    <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
            <li class="nav-item <?= $currentPath === 'dashboard' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard') ?>">
                    <i class="mdi mdi-grid-large menu-icon"></i>
                    <span class="menu-title">Dashboard</span>
                </a>
            </li>

            <li class="nav-item nav-category">Layanan Chat</li>
            <li class="nav-item <?= $currentPath === 'dashboard/support-chat' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/support-chat') ?>">
                    <i class="menu-icon mdi mdi-message-reply-text-outline"></i>
                    <span class="menu-title">Answer Chat</span>
                </a>
            </li>
            <li class="nav-item <?= $currentPath === 'dashboard/history-chat' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/history-chat') ?>">
                    <i class="menu-icon mdi mdi-history"></i>
                    <span class="menu-title">Riwayat Chat</span>
                </a>
            </li>
            <li class="nav-item <?= $currentPath === 'dashboard/scan-whatsapp' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/scan-whatsapp') ?>">
                    <i class="menu-icon mdi mdi-qrcode-scan"></i>
                    <span class="menu-title">Scan WhatsApp</span>
                </a>
            </li>

            <li class="nav-item nav-category">Dataset Chatbot</li>
            <li class="nav-item <?= str_starts_with($currentPath, 'dashboard/intents') || str_starts_with($currentPath, 'dashboard/knowledge-base') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/intents') ?>">
                    <i class="menu-icon mdi mdi-format-list-bulleted-type"></i>
                    <span class="menu-title">Intents</span>
                </a>
            </li>
            <li class="nav-item <?= str_starts_with($currentPath, 'dashboard/training-phrases') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/training-phrases') ?>">
                    <i class="menu-icon mdi mdi-message-text-outline"></i>
                    <span class="menu-title">Training Phrases</span>
                </a>
            </li>
            <li class="nav-item <?= str_starts_with($currentPath, 'dashboard/nlp-rules') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/nlp-rules') ?>">
                    <i class="menu-icon mdi mdi-tune"></i>
                    <span class="menu-title">NLP Rules</span>
                </a>
            </li>

            <li class="nav-item nav-category">Pengaturan</li>
            <li class="nav-item <?= str_starts_with($currentPath, 'dashboard/landing-page') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/landing-page') ?>">
                    <i class="mdi mdi-monitor-dashboard menu-icon"></i>
                    <span class="menu-title">Landing Page</span>
                </a>
            </li>
            <li class="nav-item <?= $currentPath === 'dashboard/profile' ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/profile') ?>">
                    <i class="mdi mdi-account-cog menu-icon"></i>
                    <span class="menu-title">Setting Profil</span>
                </a>
            </li>
        </ul>
    </nav>
