<?= $this->include('dashboard/layout/header') ?>
<?= $this->include('dashboard/layout/navbar') ?>
<?= $this->include('dashboard/layout/sidebar') ?>

<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-sm-12">
                <div class="d-sm-flex align-items-center justify-content-between border-bottom mb-4 pb-3">
                    <div>
                        <h3 class="fw-bold mb-1">History Chat</h3>
                        <p class="text-muted mb-0">Riwayat pesan WhatsApp dan Website, status jawaban chatbot, dan kandidat data latih.</p>
                    </div>
                    <button class="btn btn-outline-secondary btn-lg mt-3 mt-sm-0" type="button" id="refreshHistoryBtn">
                        <i class="mdi mdi-refresh me-1"></i> Refresh
                    </button>
                </div>

                <?php if (session('success')): ?>
                    <div class="alert alert-success"><?= esc(session('success')) ?></div>
                <?php endif; ?>
                <?php if (session('error')): ?>
                    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
                <?php endif; ?>

                <div class="history-stats mb-4" id="historyStats">
                    <div class="history-stat-card history-stat-bot">
                        <i class="mdi mdi-robot-outline"></i>
                        <span>Dijawab Bot</span>
                        <strong>0</strong>
                    </div>
                    <div class="history-stat-card history-stat-waiting">
                        <i class="mdi mdi-account-clock-outline"></i>
                        <span>Menunggu Admin</span>
                        <strong>0</strong>
                    </div>
                    <div class="history-stat-card history-stat-admin">
                        <i class="mdi mdi-account-check-outline"></i>
                        <span>Ditangani Admin</span>
                        <strong>0</strong>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-4 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <div class="row g-2 mb-3">
                                    <div class="col-7">
                                        <input class="form-control" id="searchChatInput" type="search" placeholder="Cari nomor/web/pesan">
                                    </div>
                                    <div class="col-5">
                                        <select class="form-control" id="statusFilter">
                                            <option value="">Semua</option>
                                            <option value="bot">Bot</option>
                                            <option value="waiting_cs">Menunggu Admin Sekolah</option>
                                            <option value="handled_by_cs">Ditangani Admin Sekolah</option>
                                            <option value="closed">Closed</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h4 class="card-title card-title-dash mb-0">Daftar Chat</h4>
                                    <span class="badge chat-badge-blue" id="chatCount">0</span>
                                </div>

                                <div class="history-list" id="chatList">
                                    <div class="text-muted text-center py-5">Belum ada riwayat chat.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8 grid-margin stretch-card">
                        <div class="card card-rounded">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3 gap-3">
                                    <div>
                                        <h4 class="card-title card-title-dash mb-1" id="historyTitle">Pilih chat</h4>
                                        <p class="card-subtitle card-subtitle-dash mb-0" id="historySubtitle">Detail pesan akan tampil di sini.</p>
                                    </div>
                                    <div class="history-detail-actions">
                                        <form id="deleteHistoryForm" action="#" method="post" data-confirm="Hapus riwayat chat ini beserta semua pesannya?">
                                            <?= csrf_field() ?>
                                            <button class="btn btn-outline-danger btn-sm" id="deleteHistoryBtn" type="submit" disabled>
                                                <i class="mdi mdi-delete-outline me-1"></i> Hapus
                                            </button>
                                        </form>
                                        <span class="badge chat-badge-muted" id="historyStatus">-</span>
                                    </div>
                                </div>

                                <div class="history-summary mb-3" id="historySummary">
                                    <div>
                                        <span class="text-muted d-block">Total Pesan</span>
                                        <strong>0</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted d-block">Kandidat Latih</span>
                                        <strong>0</strong>
                                    </div>
                                    <div>
                                        <span class="text-muted d-block">Butuh Admin Sekolah</span>
                                        <strong>0</strong>
                                    </div>
                                </div>

                                <div class="history-window" id="messageList">
                                    <div class="text-muted text-center py-5">Pilih salah satu chat dari daftar.</div>
                                </div>

                                <div class="alert alert-info mt-3 mb-0" id="historyMessage">
                                    Menunggu data riwayat chat.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .history-list {
            max-height: 620px;
            overflow-y: auto;
        }

        .history-stats {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .history-stat-card {
            background: #fff;
            border: 1px solid #dbe7ef;
            border-radius: 8px;
            overflow: hidden;
            padding: 16px 18px;
            position: relative;
        }

        .history-stat-card::before {
            bottom: 0;
            content: "";
            left: 0;
            position: absolute;
            top: 0;
            width: 5px;
        }

        .history-stat-card i {
            align-items: center;
            border-radius: 8px;
            display: inline-flex;
            font-size: 23px;
            height: 42px;
            justify-content: center;
            margin-bottom: 12px;
            width: 42px;
        }

        .history-stat-card span {
            color: var(--admin-muted);
            display: block;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .history-stat-card strong {
            color: var(--admin-ink);
            display: block;
            font-size: 30px;
            line-height: 1;
        }

        .history-stat-bot {
            background: #fff;
            border-color: rgba(16, 79, 134, 0.18);
        }

        .history-stat-bot::before,
        .history-stat-bot i {
            background: var(--admin-blue);
            color: #fff;
        }

        .history-stat-waiting {
            background: #fff;
            border-color: rgba(245, 183, 25, 0.34);
        }

        .history-stat-waiting::before,
        .history-stat-waiting i {
            background: var(--admin-yellow);
            color: var(--admin-ink);
        }

        .history-stat-admin {
            background: #fff;
            border-color: rgba(223, 75, 75, 0.3);
        }

        .history-stat-admin::before,
        .history-stat-admin i {
            background: var(--admin-red);
            color: #fff;
        }

        .history-item {
            border: 1px solid #edf2f7;
            border-radius: 8px;
            cursor: pointer;
            margin-bottom: 10px;
            padding: 14px;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }

        .history-item:hover,
        .history-item.active {
            background: #f8fafc;
            border-color: rgba(16, 79, 134, 0.46);
        }

        .history-last-message {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .history-window {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            max-height: 560px;
            min-height: 430px;
            overflow-y: auto;
            padding: 18px;
        }

        .history-summary {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .history-summary > div {
            background: #f8fafc;
            border: 1px solid #edf2f7;
            border-radius: 8px;
            padding: 12px 14px;
        }

        .history-bubble {
            border-radius: 8px;
            margin-bottom: 12px;
            max-width: 82%;
            padding: 12px 14px;
        }

        .history-bubble.user {
            background: #fff;
            border: 1px solid #e2e8f0;
        }

        .history-bubble.bot {
            background: rgba(16, 79, 134, 0.08);
            border: 1px solid rgba(16, 79, 134, 0.18);
        }

        .history-bubble.admin {
            background: rgba(245, 183, 25, 0.12);
            border: 1px solid rgba(245, 183, 25, 0.28);
            margin-left: auto;
        }

        .history-bubble-meta {
            color: #64748b;
            font-size: 12px;
            margin-bottom: 6px;
        }

        .history-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .history-badges .badge-chatbot {
            background: rgba(16, 79, 134, 0.12);
            color: var(--admin-blue);
        }

        .history-badges .badge-admin {
            background: rgba(245, 183, 25, 0.2);
            color: #8a5c00;
        }

        .history-badges .badge-training {
            background: rgba(223, 75, 75, 0.14);
            color: var(--admin-red);
        }

        .chat-badge-blue,
        .chat-badge-yellow,
        .chat-badge-red,
        .chat-badge-muted {
            border-radius: 999px;
            font-weight: 800;
        }

        .chat-badge-blue {
            background: rgba(16, 79, 134, 0.12);
            color: var(--admin-blue);
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

        .history-detail-actions {
            align-items: center;
            display: flex;
            flex-shrink: 0;
            gap: 10px;
        }

        @media (max-width: 767px) {
            .history-summary {
                grid-template-columns: 1fr;
            }

            .history-stats {
                grid-template-columns: 1fr;
            }

            .history-bubble {
                max-width: 100%;
            }

            .history-detail-actions {
                align-items: flex-end;
                flex-direction: column-reverse;
            }
        }
    </style>

    <script>
        const chatList = document.getElementById('chatList');
        const chatCount = document.getElementById('chatCount');
        const historyTitle = document.getElementById('historyTitle');
        const historySubtitle = document.getElementById('historySubtitle');
        const historyStatus = document.getElementById('historyStatus');
        const historySummary = document.getElementById('historySummary');
        const messageList = document.getElementById('messageList');
        const historyMessage = document.getElementById('historyMessage');
        const refreshHistoryBtn = document.getElementById('refreshHistoryBtn');
        const searchChatInput = document.getElementById('searchChatInput');
        const statusFilter = document.getElementById('statusFilter');
        const deleteHistoryForm = document.getElementById('deleteHistoryForm');
        const deleteHistoryBtn = document.getElementById('deleteHistoryBtn');
        const historyStats = document.getElementById('historyStats');

        let chats = [];
        let selectedChatId = null;
        let searchTimer = null;

        function escapeHtml(value) {
            return String(value || '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));
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
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Jakarta',
            });
        }

        function statusBadge(status) {
            const map = {
                bot: ['Bot', 'chat-badge-blue'],
                waiting_cs: ['Menunggu Admin Sekolah', 'chat-badge-yellow'],
                handled_by_cs: ['Ditangani Admin Sekolah', 'chat-badge-red'],
                closed: ['Closed', 'chat-badge-muted'],
            };
            return map[status] || [status || '-', 'chat-badge-muted'];
        }

        function setHistoryMessage(text, type = 'info') {
            historyMessage.className = `alert alert-${type} mt-3 mb-0`;
            historyMessage.textContent = text;
        }

        function renderHistoryStats() {
            const counts = chats.reduce((result, chat) => {
                result[chat.status] = (result[chat.status] || 0) + 1;
                return result;
            }, {});

            historyStats.innerHTML = `
                <div class="history-stat-card history-stat-bot">
                    <i class="mdi mdi-robot-outline"></i>
                    <span>Dijawab Bot</span>
                    <strong>${counts.bot || 0}</strong>
                </div>
                <div class="history-stat-card history-stat-waiting">
                    <i class="mdi mdi-account-clock-outline"></i>
                    <span>Menunggu Admin</span>
                    <strong>${counts.waiting_cs || 0}</strong>
                </div>
                <div class="history-stat-card history-stat-admin">
                    <i class="mdi mdi-account-check-outline"></i>
                    <span>Ditangani Admin</span>
                    <strong>${counts.handled_by_cs || 0}</strong>
                </div>
            `;
        }

        function renderChats() {
            chatCount.textContent = chats.length;
            renderHistoryStats();

            if (!chats.length) {
                chatList.innerHTML = '<div class="text-muted text-center py-5">Belum ada riwayat chat.</div>';
                return;
            }

            chatList.innerHTML = chats.map((chat) => {
                const active = Number(chat.id) === Number(selectedChatId) ? 'active' : '';
                const [label, type] = statusBadge(chat.status);
                const title = chat.contactName || (chat.waNumber && chat.waNumber.startsWith('WEB-') ? 'Pengunjung Website' : chat.waNumber);
                const source = chat.waNumber && chat.waNumber.startsWith('WEB-') ? 'Website' : chat.waNumber;

                return `
                    <div class="history-item ${active}" data-chat-id="${chat.id}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <strong>${escapeHtml(title)}</strong>
                            <span class="badge ${type}">${label}</span>
                        </div>
                        <p class="text-muted mb-2 history-last-message">${escapeHtml(chat.lastMessage || '-')}</p>
                        <div class="d-flex align-items-center justify-content-between">
                            <small class="text-muted">${escapeHtml(source || '-')}</small>
                            <small class="text-muted">${chat.messageCount} pesan</small>
                        </div>
                        <small class="text-muted">${formatTime(chat.lastMessageAt || chat.updatedAt)}</small>
                    </div>
                `;
            }).join('');
        }

        function renderEmptyDetail() {
            historyTitle.textContent = 'Pilih chat';
            historySubtitle.textContent = 'Detail pesan akan tampil di sini.';
            historyStatus.textContent = '-';
            historyStatus.className = 'badge chat-badge-muted';
            deleteHistoryForm.action = '#';
            deleteHistoryBtn.disabled = true;
            historySummary.innerHTML = `
                <div><span class="text-muted d-block">Total Pesan</span><strong>0</strong></div>
                <div><span class="text-muted d-block">Kandidat Latih</span><strong>0</strong></div>
                <div><span class="text-muted d-block">Butuh Admin Sekolah</span><strong>0</strong></div>
            `;
            messageList.innerHTML = '<div class="text-muted text-center py-5">Pilih salah satu chat dari daftar.</div>';
        }

        function renderMessages(chat) {
            const [label, type] = statusBadge(chat.status);
            const messages = chat.messages || [];
            const trainingCount = messages.filter((message) => message.isTrainingCandidate).length;
            const needsCsCount = messages.filter((message) => message.needsCs).length;

            historyTitle.textContent = chat.contactName || chat.waNumber;
            const sourceLabel = chat.waNumber && chat.waNumber.startsWith('WEB-') ? 'Website' : chat.waNumber;
            historySubtitle.textContent = `${sourceLabel} - ${formatTime(chat.lastMessageAt || chat.updatedAt)}`;
            historyStatus.textContent = label;
            historyStatus.className = `badge ${type}`;
            deleteHistoryForm.action = `<?= site_url('dashboard/history-chat') ?>/${chat.id}/delete`;
            deleteHistoryBtn.disabled = false;
            historySummary.innerHTML = `
                <div><span class="text-muted d-block">Total Pesan</span><strong>${messages.length}</strong></div>
                <div><span class="text-muted d-block">Kandidat Latih</span><strong>${trainingCount}</strong></div>
                <div><span class="text-muted d-block">Butuh Admin Sekolah</span><strong>${needsCsCount}</strong></div>
            `;

            if (!messages.length) {
                messageList.innerHTML = '<div class="text-muted text-center py-5">Belum ada pesan untuk chat ini.</div>';
                return;
            }

            messageList.innerHTML = messages.map((message) => {
                const badges = [];

                if (message.sender !== 'bot' && message.answeredByChatbot) {
                    badges.push('<span class="badge badge-chatbot">Dijawab Chatbot</span>');
                }

                if (message.sender !== 'bot' && message.needsCs) {
                    badges.push('<span class="badge badge-admin">Butuh Admin Sekolah</span>');
                }

                if (message.sender !== 'bot' && message.isTrainingCandidate) {
                    badges.push('<span class="badge badge-training">Data Latih</span>');
                }

                return `
                    <div class="history-bubble ${message.sender}">
                        <div class="history-bubble-meta">${message.sender.toUpperCase()} - ${formatTime(message.at)}</div>
                        <div>${escapeHtml(message.body)}</div>
                        ${badges.length ? `<div class="history-badges">${badges.join('')}</div>` : ''}
                    </div>
                `;
            }).join('');
            messageList.scrollTop = messageList.scrollHeight;
        }

        async function loadChats() {
            const params = new URLSearchParams();

            if (searchChatInput.value.trim()) {
                params.set('q', searchChatInput.value.trim());
            }

            if (statusFilter.value) {
                params.set('status', statusFilter.value);
            }

            try {
                const response = await fetch(`<?= site_url('api/wa/chats') ?>?${params.toString()}`);
                const data = await response.json();
                chats = data.chats || [];

                if (selectedChatId && !chats.some((chat) => Number(chat.id) === Number(selectedChatId))) {
                    selectedChatId = null;
                    renderEmptyDetail();
                }

                renderChats();
                setHistoryMessage('Riwayat chat berhasil dimuat.', 'success');
            } catch (error) {
                setHistoryMessage('Gagal memuat riwayat chat dari database.', 'danger');
            }
        }

        async function loadChatDetail(chatId) {
            try {
                const response = await fetch(`<?= site_url('api/wa/chats') ?>/${chatId}`);
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Chat tidak ditemukan.');
                }

                selectedChatId = chatId;
                renderChats();
                renderMessages(data.chat);
                setHistoryMessage('Detail chat berhasil dimuat.', 'success');
            } catch (error) {
                setHistoryMessage(error.message, 'danger');
            }
        }

        chatList.addEventListener('click', (event) => {
            const item = event.target.closest('[data-chat-id]');

            if (!item) {
                return;
            }

            loadChatDetail(item.dataset.chatId);
        });

        refreshHistoryBtn.addEventListener('click', () => {
            loadChats();

            if (selectedChatId) {
                loadChatDetail(selectedChatId);
            }
        });

        searchChatInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(loadChats, 300);
        });

        statusFilter.addEventListener('change', loadChats);

        renderEmptyDetail();
        loadChats();
    </script>

    <?= $this->include('dashboard/layout/footer') ?>
