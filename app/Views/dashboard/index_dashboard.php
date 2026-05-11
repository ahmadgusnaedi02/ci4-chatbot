<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>
<?php
$stats = $stats ?? [
    'questioners' => 0,
    'intents' => 0,
    'datasets' => 0,
    'chats' => 0,
];
$chartLabels = $chartLabels ?? [];
$chatChartData = $chatChartData ?? [];
$questionerChartData = $questionerChartData ?? [];
?>

<div class="main-panel">
    <div class="content-wrapper admin-content">
        <div class="admin-page-head">
            <div>
                <p class="admin-eyebrow mb-2">SPMB Control Center</p>
                <h2 class="admin-page-title mb-2">Dashboard Admin</h2>
                <p class="admin-page-subtitle mb-0">Pantau koneksi WhatsApp, antrian chat, dan riwayat percakapan calon
                    peserta didik.</p>
            </div>
            <a class="btn admin-primary-btn mt-3 mt-md-0" href="<?= site_url('/') ?>" target="_blank" rel="noopener">
                <i class="mdi mdi-open-in-new me-1"></i> Lihat Landing Page
            </a>
        </div>

        <div class="admin-hero-panel">
            <div class="admin-hero-copy">
                <span class="admin-status-pill"><i class="mdi mdi-school-outline"></i> SMPS Plus Fajar Sentosa</span>
                <h3>Selamat datang, <?= esc(session('admin_name') ?? 'Admin Sekolah') ?></h3>
                <p>Gunakan panel ini untuk menjaga layanan informasi SPMB tetap cepat, jelas, dan siap membantu orang
                    tua maupun calon siswa.</p>
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
                        <div class="admin-stat-icon blue"><i class="mdi mdi-account-question-outline"></i></div>
                        <p>Jumlah Penanya</p>
                        <h3><?= number_format((int) $stats['questioners'], 0, ',', '.') ?></h3>
                        <span>Total kontak yang pernah bertanya.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon red"><i class="mdi mdi-brain"></i></div>
                        <p>Jumlah Intent</p>
                        <h3><?= number_format((int) $stats['intents'], 0, ',', '.') ?></h3>
                        <span>Kategori jawaban yang tersedia.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon teal"><i class="mdi mdi-database-outline"></i></div>
                        <p>Jumlah Dataset</p>
                        <h3><?= number_format((int) $stats['datasets'], 0, ',', '.') ?></h3>
                        <span>Contoh kalimat latih chatbot.</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-3 grid-margin stretch-card">
                <div class="card admin-stat-card">
                    <div class="card-body">
                        <div class="admin-stat-icon yellow"><i class="mdi mdi-message-text-outline"></i></div>
                        <p>Jumlah Chat</p>
                        <h3><?= number_format((int) $stats['chats'], 0, ',', '.') ?></h3>
                        <span>Total pesan masuk dan keluar.</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card admin-card admin-chart-card">
                    <div class="card-body">
                        <div class="admin-card-head admin-chart-head">
                            <div>
                                <h4>Jumlah Chat per Hari</h4>
                                <p>Aktivitas pesan masuk dan keluar selama 7 hari terakhir.</p>
                            </div>
                            <span class="admin-chart-badge">7 Hari</span>
                        </div>
                        <div class="admin-chart-wrap">
                            <canvas id="dailyChatChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card admin-card admin-chart-card">
                    <div class="card-body">
                        <div class="admin-card-head admin-chart-head">
                            <div>
                                <h4>Penanya per Hari</h4>
                                <p>Kontak baru yang memulai percakapan.</p>
                            </div>
                            <span class="admin-chart-badge teal">Harian</span>
                        </div>
                        <div class="admin-chart-wrap compact">
                            <canvas id="dailyQuestionerChart"></canvas>
                        </div>
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
                                <p>Urutan kerja harian untuk menjaga respon SPMB tetap rapi.</p>
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

    <script>
        window.dashboardChartData = {
            labels: <?= json_encode($chartLabels, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
            chats: <?= json_encode($chatChartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
            questioners: <?= json_encode($questionerChartData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
        };

        document.addEventListener('DOMContentLoaded', function () {
            if (!window.Chart || !window.dashboardChartData) {
                return;
            }

            const chartData = window.dashboardChartData;
            const sharedOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#657487',
                            font: {
                                weight: 700
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(101, 116, 135, 0.14)'
                        },
                        ticks: {
                            color: '#657487',
                            precision: 0
                        }
                    }
                }
            };

            const chatCanvas = document.getElementById('dailyChatChart');
            if (chatCanvas) {
                new Chart(chatCanvas, {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.chats,
                            borderColor: '#104f86',
                            backgroundColor: 'rgba(16, 79, 134, 0.14)',
                            borderWidth: 3,
                            fill: true,
                            pointBackgroundColor: '#104f86',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            tension: 0.35
                        }]
                    },
                    options: sharedOptions
                });
            }

            const questionerCanvas = document.getElementById('dailyQuestionerChart');
            if (questionerCanvas) {
                new Chart(questionerCanvas, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            data: chartData.questioners,
                            backgroundColor: '#5f9ea0',
                            borderRadius: 8,
                            maxBarThickness: 34
                        }]
                    },
                    options: sharedOptions
                });
            }
        });
    </script>

    <?= $this->include('dashboard/layout/footer') ?>
