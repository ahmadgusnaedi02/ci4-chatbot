<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-3">
                    <div>
                        <h3 class="fw-bold mb-1">Answer Chat</h3>
                        <p class="text-muted mb-0">Balas pesan WhatsApp dan Website yang sudah minta terhubung dengan admin sekolah.</p>
                    </div>
                    <button class="btn btn-outline-secondary btn-lg mt-3 mt-sm-0" type="button" id="refreshTicketsBtn">
                        <i class="mdi mdi-refresh me-1"></i> Refresh
                    </button>
                </div>

                <div class="row">
                    <div class="col-lg-4 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="card-title card-title-dash mb-0">Antrian Admin Sekolah</h4>
                                    <span class="badge chat-badge-yellow" id="ticketCount">0</span>
                                </div>
                                <div class="support-ticket-list" id="ticketList">
                                    <div class="text-muted text-center py-5">Belum ada chat yang menunggu admin sekolah.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                    <div>
                                        <h4 class="card-title card-title-dash mb-1" id="chatTitle">Pilih chat</h4>
                                        <p class="card-subtitle card-subtitle-dash mb-0" id="chatSubtitle">Detail percakapan akan tampil di sini.</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-outline-danger btn-sm" type="button" id="endChatBtn" disabled>
                                            <i class="mdi mdi-close-circle-outline me-1"></i> Akhiri chat
                                        </button>
                                        <span class="badge chat-badge-muted" id="chatStatus">-</span>
                                    </div>
                                </div>

                                <div class="support-chat-window" id="chatMessages">
                                    <div class="text-muted text-center py-5">Pilih salah satu antrian admin sekolah.</div>
                                </div>

                                <form class="support-reply-form mt-3" id="replyForm">
                                    <textarea class="form-control" id="replyMessage" rows="3" placeholder="Tulis balasan admin..." disabled></textarea>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="text-muted" id="replyInfo">Pilih chat aktif untuk membalas.</span>
                                        <button class="btn btn-primary text-white btn-lg" type="submit" id="sendReplyBtn" disabled>
                                            <i class="mdi mdi-send me-1"></i> Kirim
                                        </button>
                                    </div>
                                </form>

                                <div class="alert alert-info mt-3 mb-0" id="supportMessage">
                                    Menunggu data antrian admin sekolah.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .support-ticket-list {
            max-height: 560px;
            overflow-y: auto;
        }

        .support-ticket-item {
            border: 1px solid #edf2f7;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 10px;
            padding: 14px;
            transition: border-color 0.15s ease, background-color 0.15s ease;
        }

        .support-ticket-item:hover,
        .support-ticket-item.active {
            background: #f8fafc;
            border-color: rgba(16, 79, 134, 0.46);
        }

        .support-chat-window {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            min-height: 420px;
            max-height: 520px;
            overflow-y: auto;
            padding: 18px;
        }

        .support-bubble {
            border-radius: 8px;
            margin-bottom: 12px;
            max-width: 78%;
            padding: 12px 14px;
        }

        .support-bubble.user {
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .support-bubble.bot {
            background: rgba(16, 79, 134, 0.08);
            border: 1px solid rgba(16, 79, 134, 0.18);
        }

        .support-bubble.admin {
            background: rgba(245, 183, 25, 0.12);
            border: 1px solid rgba(245, 183, 25, 0.28);
            margin-left: auto;
        }

        .chat-badge-yellow,
        .chat-badge-red,
        .chat-badge-muted {
            border-radius: 999px;
            font-weight: 800;
        }

        .chat-badge-yellow {
            background: rgba(245, 183, 25, 0.2);
            color: #8a5c00;
        }

        .chat-badge-red {
            background: rgba(223, 75, 75, 0.14);
            color: var(--admin-red);
        }

        .chat-badge-muted {
            background: #edf2f7;
            color: var(--admin-muted);
        }

        .support-bubble-meta {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 4px;
        }
    </style>

    <script src="http://localhost:3001/socket.io/socket.io.js"></script>
    <script>
        const ciSupportApiBase = '<?= site_url('api/wa/support-chats') ?>';
        const nodeSupportApiBase = 'http://localhost:3001/api/support-chats';
        const ticketList = document.getElementById('ticketList');
        const ticketCount = document.getElementById('ticketCount');
        const chatTitle = document.getElementById('chatTitle');
        const chatSubtitle = document.getElementById('chatSubtitle');
        const chatStatus = document.getElementById('chatStatus');
        const chatMessages = document.getElementById('chatMessages');
        const replyForm = document.getElementById('replyForm');
        const replyMessage = document.getElementById('replyMessage');
        const sendReplyBtn = document.getElementById('sendReplyBtn');
        const endChatBtn = document.getElementById('endChatBtn');
        const supportMessage = document.getElementById('supportMessage');
        const refreshTicketsBtn = document.getElementById('refreshTicketsBtn');

        let tickets = [];
        let selectedTicketId = null;

        function setSupportMessage(text, type = 'info') {
            supportMessage.className = `alert alert-${type} mt-3 mb-0`;
            supportMessage.textContent = text;
        }

        function parseAppTime(value) {
            if (!value) {
                return null;
            }

            const text = String(value);

            if (/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(text)) {
                return new Date(`${text.replace(' ', 'T')}+07:00`);
            }

            return new Date(text);
        }

        function formatTime(value) {
            if (!value) {
                return '-';
            }

            return parseAppTime(value).toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta',
            });
        }

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));
        }

        function isWebTicket(ticket) {
            return String(ticket?.from || '').startsWith('WEB-');
        }

        function supportActionUrl(ticket, action) {
            const base = isWebTicket(ticket) ? ciSupportApiBase : nodeSupportApiBase;

            return `${base}/${encodeURIComponent(ticket.id)}/${action}`;
        }

        function renderTickets() {
            ticketCount.textContent = tickets.filter((ticket) => ticket.status === 'waiting_admin').length;

            if (!tickets.length) {
                ticketList.innerHTML = '<div class="text-muted text-center py-5">Belum ada chat yang menunggu admin sekolah.</div>';
                return;
            }

            ticketList.innerHTML = tickets.map((ticket) => {
                const lastMessage = ticket.messages[ticket.messages.length - 1];
                const active = ticket.id === selectedTicketId ? 'active' : '';
                const badge = ticket.status === 'waiting_admin' ? 'chat-badge-yellow' : 'chat-badge-red';
                const statusText = ticket.status === 'waiting_admin' ? 'Menunggu' : 'Terjawab';

                return `
                    <div class="support-ticket-item ${active}" data-ticket-id="${ticket.id}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong>${escapeHtml(ticket.from)}</strong>
                            <span class="badge ${badge}">${statusText}</span>
                        </div>
                        <p class="text-muted mb-2">${lastMessage ? escapeHtml(lastMessage.body) : '-'}</p>
                        <small class="text-muted">${formatTime(ticket.updatedAt)}</small>
                    </div>
                `;
            }).join('');
        }

        function renderSelectedTicket() {
            const ticket = tickets.find((item) => item.id === selectedTicketId);

            if (!ticket) {
                chatTitle.textContent = 'Pilih chat';
                chatSubtitle.textContent = 'Detail percakapan akan tampil di sini.';
                chatStatus.textContent = '-';
                chatStatus.className = 'badge chat-badge-muted';
                chatMessages.innerHTML = '<div class="text-muted text-center py-5">Pilih salah satu antrian admin sekolah.</div>';
                replyMessage.disabled = true;
                sendReplyBtn.disabled = true;
                endChatBtn.disabled = true;
                return;
            }

            const isActive = ticket.status === 'waiting_admin';

            chatTitle.textContent = ticket.from;
            chatSubtitle.textContent = `Dibuat ${formatTime(ticket.createdAt)}`;
            chatStatus.textContent = isActive ? 'Aktif Admin Sekolah' : 'Selesai';
            chatStatus.className = `badge ${isActive ? 'chat-badge-yellow' : 'chat-badge-red'}`;
            replyMessage.disabled = !isActive;
            sendReplyBtn.disabled = !isActive;
            endChatBtn.disabled = !isActive;

            chatMessages.innerHTML = ticket.messages.map((message) => `
                <div class="support-bubble ${message.sender}">
                    <div class="support-bubble-meta">${message.sender.toUpperCase()} - ${formatTime(message.at)}</div>
                    <div>${escapeHtml(message.body)}</div>
                </div>
            `).join('');
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        async function loadTickets(quiet = false) {
            try {
                const response = await fetch(ciSupportApiBase);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal memuat antrian admin sekolah.');
                }

                tickets = data.tickets || [];

                if (selectedTicketId && !tickets.some((ticket) => ticket.id === selectedTicketId)) {
                    selectedTicketId = null;
                }

                renderTickets();
                renderSelectedTicket();
                if (!quiet) {
                    setSupportMessage('Data antrian admin sekolah berhasil dimuat.', 'success');
                }
            } catch (error) {
                setSupportMessage(error.message, 'danger');
            }
        }

        ticketList.addEventListener('click', (event) => {
            const item = event.target.closest('[data-ticket-id]');

            if (!item) {
                return;
            }

            selectedTicketId = item.dataset.ticketId;
            renderTickets();
            renderSelectedTicket();
        });

        replyForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const text = replyMessage.value.trim();
            const ticket = tickets.find((item) => item.id === selectedTicketId);

            if (!ticket || !text) {
                return;
            }

            sendReplyBtn.disabled = true;
            setSupportMessage('Mengirim balasan admin...', 'info');

            try {
                const response = await fetch(supportActionUrl(ticket, 'reply'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: text }),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengirim balasan.');
                }

                replyMessage.value = '';
                await loadTickets();
                setSupportMessage('Balasan admin berhasil dikirim.', 'success');
            } catch (error) {
                setSupportMessage(error.message, 'danger');
                sendReplyBtn.disabled = false;
            }
        });

        endChatBtn.addEventListener('click', async () => {
            const ticket = tickets.find((item) => item.id === selectedTicketId);

            if (!ticket || ticket.status !== 'waiting_admin') {
                return;
            }

            endChatBtn.disabled = true;
            sendReplyBtn.disabled = true;
            setSupportMessage('Mengakhiri chat admin sekolah...', 'info');

            try {
                const response = await fetch(supportActionUrl(ticket, 'end'), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({}),
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Gagal mengakhiri chat.');
                }

                await loadTickets();
                setSupportMessage('Chat admin sekolah diakhiri. Pesan berikutnya akan dijawab bot lagi.', 'success');
            } catch (error) {
                setSupportMessage(error.message, 'danger');
                endChatBtn.disabled = false;
                sendReplyBtn.disabled = false;
            }
        });

        refreshTicketsBtn.addEventListener('click', loadTickets);
        window.setInterval(() => loadTickets(true), 5000);

        if (typeof io !== 'undefined') {
            const socket = io('http://localhost:3001', {
                transports: ['websocket', 'polling'],
            });

            socket.on('support:list', (payload) => {
                tickets = payload.tickets || [];
                renderTickets();
                renderSelectedTicket();
            });

            socket.on('support:changed', () => {
                loadTickets();
            });
        }

        loadTickets();
    </script>

    <?= $this->include('dashboard/layout/footer') ?>
