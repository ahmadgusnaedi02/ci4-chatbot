<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-3">
                    <div>
                        <h3 class="fw-bold mb-1">Scan WhatsApp</h3>
                        <p class="text-muted mb-0">Hubungkan nomor WhatsApp ke chatbot melalui QR WhatsApp Web.</p>
                    </div>
                    <div class="d-flex gap-2 mt-3 mt-sm-0">
                        <button class="btn btn-outline-secondary btn-lg" type="button" id="refreshQrBtn">
                            <i class="mdi mdi-refresh me-1"></i> Refresh
                        </button>
                        <button class="btn btn-outline-danger btn-lg" type="button" id="logoutWaBtn">
                            <i class="mdi mdi-logout me-1"></i> Logout
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div>
                                        <h4 class="card-title card-title-dash mb-1">QR Login</h4>
                                        <p class="card-subtitle card-subtitle-dash mb-0">Buka WhatsApp di HP, lalu scan
                                            kode ini.</p>
                                    </div>
                                    <span class="badge badge-opacity-warning" id="waStatusBadge">Menunggu</span>
                                </div>

                                <div class="wa-qr-shell">
                                    <div class="wa-qr-box" id="qrPlaceholder">
                                        <i class="mdi mdi-qrcode-scan"></i>
                                        <p class="mb-0">Menunggu QR dari Node.js...</p>
                                    </div>
                                    <img src="" alt="QR WhatsApp" class="wa-qr-image d-none" id="qrImage">
                                </div>

                                <div class="alert alert-info mt-4 mb-0" id="waMessage">
                                    Jalankan service Node.js lewat terminal terlebih dahulu, lalu QR akan muncul otomatis.
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <h4 class="card-title card-title-dash mb-3">Status Koneksi</h4>

                                <div class="list-wrapper">
                                    <ul class="todo-list todo-list-rounded">
                                        <li class="d-block">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>Service Node.js</span>
                                                <span class="badge badge-opacity-danger" id="nodeStatus">Offline</span>
                                            </div>
                                        </li>
                                        <li class="d-block">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>WhatsApp Client</span>
                                                <span class="badge badge-opacity-warning"
                                                    id="clientStatus">Loading</span>
                                            </div>
                                        </li>
                                        <li class="d-block border-bottom-0">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span>Nomor Terhubung</span>
                                                <span class="text-muted" id="connectedNumber">-</span>
                                            </div>
                                        </li>
                                    </ul>
                                    <div class="mt-4">
                                        <h6 class="fw-semibold mb-2">Cara Scan</h6>
                                        <div class="text-muted">
                                            <p class="mb-2">1. Buka WhatsApp di HP.</p>
                                            <p class="mb-2">2. Masuk ke Perangkat tertaut.</p>
                                            <p class="mb-0">3. Scan QR sampai status berubah menjadi terhubung.</p>
                                        </div>
                                    </div>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .wa-qr-shell {
            align-items: center;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            display: flex;
            justify-content: center;
            min-height: 360px;
            padding: 24px;
        }

        .wa-qr-box {
            color: #64748b;
            text-align: center;
        }

        .wa-qr-box i {
            color: #25d366;
            display: block;
            font-size: 64px;
            line-height: 1;
            margin-bottom: 12px;
        }

        .wa-qr-image {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            max-width: 320px;
            padding: 16px;
            width: 100%;
        }
    </style>

    <script src="http://localhost:3001/socket.io/socket.io.js"></script>
    <script>
        const qrImage = document.getElementById('qrImage');
        const qrPlaceholder = document.getElementById('qrPlaceholder');
        const waMessage = document.getElementById('waMessage');
        const waStatusBadge = document.getElementById('waStatusBadge');
        const nodeStatus = document.getElementById('nodeStatus');
        const clientStatus = document.getElementById('clientStatus');
        const connectedNumber = document.getElementById('connectedNumber');
        const refreshQrBtn = document.getElementById('refreshQrBtn');
        const logoutWaBtn = document.getElementById('logoutWaBtn');
        const serverStatusUrl = '<?= site_url('dashboard/scan-whatsapp/server-status') ?>';

        function setBadge(el, text, type) {
            el.className = `badge badge-opacity-${type}`;
            el.textContent = text;
        }

        function setMessage(text, type = 'info') {
            waMessage.className = `alert alert-${type} mt-4 mb-0`;
            waMessage.textContent = text;
        }

        function showQr(src) {
            qrImage.src = src;
            qrImage.classList.remove('d-none');
            qrPlaceholder.classList.add('d-none');
        }

        function hideQr() {
            qrImage.src = '';
            qrImage.classList.add('d-none');
            qrPlaceholder.classList.remove('d-none');
        }

        async function refreshServerStatus() {
            try {
                const response = await fetch(serverStatusUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const data = await response.json();

                if (data.running) {
                    setBadge(nodeStatus, 'Online', 'success');
                    return true;
                }

                setBadge(nodeStatus, 'Offline', 'danger');
                return false;
            } catch (error) {
                setBadge(nodeStatus, 'Offline', 'danger');
                return false;
            }
        }

        if (typeof io === 'undefined') {
            setBadge(nodeStatus, 'Offline', 'danger');
            setBadge(clientStatus, 'Belum jalan', 'danger');
            setBadge(waStatusBadge, 'Offline', 'danger');
            setMessage('Service Node.js belum berjalan. Jalankan npm start di folder whatsapp-server.', 'danger');
        } else {
            const socket = io('http://localhost:3001', {
                transports: ['websocket', 'polling'],
                reconnectionAttempts: 10,
            });

            socket.on('connect', () => {
                setBadge(nodeStatus, 'Online', 'success');
                setMessage('Service Node.js tersambung. Menunggu status WhatsApp...', 'info');
            });

            socket.on('disconnect', () => {
                setBadge(nodeStatus, 'Offline', 'danger');
                setBadge(clientStatus, 'Terputus', 'danger');
                setBadge(waStatusBadge, 'Offline', 'danger');
                hideQr();
                setMessage('Service Node.js tidak terhubung. Pastikan server berjalan di port 3001.', 'danger');
            });

            socket.on('wa:status', (payload) => {
                const status = payload.status || 'loading';
                const number = payload.number || '-';
                connectedNumber.textContent = number;

                if (status === 'ready') {
                    setBadge(clientStatus, 'Terhubung', 'success');
                    setBadge(waStatusBadge, 'Ready', 'success');
                    hideQr();
                    setMessage(`WhatsApp sudah terhubung${number !== '-' ? ` sebagai ${number}` : ''}.`, 'success');
                    return;
                }

                if (status === 'qr') {
                    setBadge(clientStatus, 'Perlu Scan', 'warning');
                    setBadge(waStatusBadge, 'Scan QR', 'warning');
                    setMessage('QR baru diterima. Scan lewat WhatsApp sebelum kadaluarsa.', 'warning');
                    return;
                }

                setBadge(clientStatus, 'Loading', 'warning');
                setBadge(waStatusBadge, 'Menunggu', 'warning');
            });

            socket.on('wa:qr', (payload) => {
                showQr(payload.image);
                setBadge(clientStatus, 'Perlu Scan', 'warning');
                setBadge(waStatusBadge, 'Scan QR', 'warning');
                setMessage('QR siap discan. Jika kadaluarsa, klik Refresh.', 'warning');
            });

            socket.on('wa:error', (payload) => {
                setMessage(payload.message || 'Terjadi kesalahan pada service WhatsApp.', 'danger');
            });
        }

        refreshQrBtn.addEventListener('click', async () => {
            setMessage('Meminta QR baru...', 'info');
            try {
                await fetch('http://localhost:3001/api/restart', { method: 'POST' });
            } catch (error) {
                setMessage('Tidak bisa menghubungi service Node.js.', 'danger');
            }
        });

        logoutWaBtn.addEventListener('click', async () => {
            setMessage('Memutus sesi WhatsApp...', 'info');
            try {
                await fetch('http://localhost:3001/api/logout', { method: 'POST' });
            } catch (error) {
                setMessage('Tidak bisa menghubungi service Node.js.', 'danger');
            }
        });

        refreshServerStatus();
    </script>

    <?= $this->include('dashboard/layout/footer') ?>
