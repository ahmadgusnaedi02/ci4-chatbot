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
            <li class="nav-item <?= str_starts_with($currentPath, 'dashboard/keywords') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/keywords') ?>">
                    <i class="menu-icon mdi mdi-key-variant"></i>
                    <span class="menu-title">Keywords</span>
                </a>
            </li>
            <li class="nav-item <?= str_starts_with($currentPath, 'dashboard/nlp-rules') ? 'active' : '' ?>">
                <a class="nav-link" href="<?= site_url('dashboard/nlp-rules') ?>">
                    <i class="menu-icon mdi mdi-tune"></i>
                    <span class="menu-title">NLP Rules</span>
                </a>
            </li>
            <li class="nav-item nav-category">Layanan Chat</li>
            <?php $chatMenuActive = in_array($currentPath, ['dashboard/history-chat', 'dashboard/support-chat'], true); ?>
            <li class="nav-item <?= $chatMenuActive ? 'active' : '' ?>">
                <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="<?= $chatMenuActive ? 'true' : 'false' ?>"
                    aria-controls="form-elements">
                    <i class="menu-icon mdi mdi-card-text-outline"></i>
                    <span class="menu-title">Chat</span>
                    <i class="menu-arrow"></i>
                </a>
                <div class="collapse <?= $chatMenuActive ? 'show' : '' ?>" id="form-elements">
                    <ul class="nav flex-column sub-menu">
                        <li class="nav-item"><a class="nav-link" href="<?= site_url('dashboard/history-chat') ?>">Riwayat
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
