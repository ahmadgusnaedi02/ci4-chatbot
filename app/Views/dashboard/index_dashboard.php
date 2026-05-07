<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">PPDB Control Center</p>
                <h2 class="admin-page-title mb-2">Dashboard Admin</h2>
                <p class="admin-page-subtitle mb-0">Pantau koneksi WhatsApp, antrian chat, dan riwayat percakapan calon peserta didik.</p>
            </div>
            <a class="btn admin-primary-btn mt-3 mt-md-0" href="<?= site_url('/') ?>" target="_blank" rel="noopener">
                <i class="mdi mdi-open-in-new me-1"></i> Lihat Landing Page
            </a>
        </div>

        <div class="admin-hero-panel">
            <div class="admin-hero-copy">
                <span class="admin-status-pill"><i class="mdi mdi-school-outline"></i> SMPS Plus Fajar Sentosa</span>
                <h3>Selamat datang, <?= esc(session('admin_name') ?? 'Admin Sekolah') ?></h3>
                <p>Gunakan panel ini untuk menjaga layanan informasi PPDB tetap cepat, jelas, dan siap membantu orang tua maupun calon siswa.</p>
            </div>
            <div class="admin-hero-actions">
                <a class="btn admin-light-btn" href="<?= site_url('dashboard/scan-whatsapp') ?>">
                    <i class="mdi mdi-qrcode-scan me-1"></i> Scan WhatsApp
                </a>
                <a class="btn admin-yellow-btn" href="<?= site_url('dashboard/support-chat') ?>">
                    <i class="mdi mdi-message-reply-text-outline me-1"></i> Answer Chat
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon blue"><i class="mdi mdi-whatsapp"></i></div>
                        <p>Koneksi WhatsApp</p>
                        <h3>Siap Dicek</h3>
                        <span>Scan QR untuk mengaktifkan client.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon red"><i class="mdi mdi-headset"></i></div>
                        <p>Antrian CS</p>
                        <h3>Live Support</h3>
                        <span>Balas chat yang meminta admin.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon teal"><i class="mdi mdi-history"></i></div>
                        <p>Riwayat Chat</p>
                        <h3>Terekam</h3>
                        <span>Cari percakapan dan kandidat data latih.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon yellow"><i class="mdi mdi-robot-outline"></i></div>
                        <p>Chatbot PPDB</p>
                        <h3>Aktif</h3>
                        <span>Menjawab informasi pendaftaran.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="admin-card-head">
                            <div>
                                <h4>Workflow Layanan</h4>
                                <p>Urutan kerja harian untuk menjaga respon PPDB tetap rapi.</p>
                            </div>
                        </div>

                        <div class="admin-workflow">
                            <a class="admin-workflow-item" href="<?= site_url('dashboard/scan-whatsapp') ?>">
                                <span>1</span>
                                <div>
                                    <h5>Hubungkan WhatsApp</h5>
                                    <p>Pastikan service Node.js aktif, lalu scan QR WhatsApp Web.</p>
                                </div>
                                <i class="mdi mdi-chevron-right"></i>
                            </a>
                            <a class="admin-workflow-item" href="<?= site_url('dashboard/support-chat') ?>">
                                <span>2</span>
                                <div>
                                    <h5>Tangani Chat CS</h5>
                                    <p>Balas percakapan yang tidak bisa diselesaikan otomatis oleh bot.</p>
                                </div>
                                <i class="mdi mdi-chevron-right"></i>
                            </a>
                            <a class="admin-workflow-item" href="<?= site_url('dashboard/history-chat') ?>">
                                <span>3</span>
                                <div>
                                    <h5>Review Riwayat</h5>
                                    <p>Telusuri pesan masuk dan cek kandidat pertanyaan untuk data latih.</p>
                                </div>
                                <i class="mdi mdi-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card admin-card">
                    <div class="card-body">
                        <div class="admin-card-head">
                            <div>
                                <h4>Prioritas Hari Ini</h4>
                                <p>Checklist singkat sebelum layanan dibuka.</p>
                            </div>
                        </div>

                        <div class="admin-checklist">
                            <div>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Pastikan landing page bisa diakses.</span>
                            </div>
                            <div>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Aktifkan WhatsApp client.</span>
                            </div>
                            <div>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Cek antrian chat yang menunggu admin.</span>
                            </div>
                            <div>
                                <i class="mdi mdi-check-circle-outline"></i>
                                <span>Review riwayat chat setelah jam layanan.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?= $this->include('dashboard/layout/footer') ?>
