<!-- Chatbot Toggle Button -->
<button id="chatbot-toggler" class="chatbot-toggler" type="button">
    <span class="material-symbols-rounded open-icon">mode_comment</span>
    <span class="material-symbols-rounded close-icon">close</span>
</button>

<div class="chatbot-popup" id="chatbot-popup">
    <!-- chatbot header -->
    <div class="chat-header">
        <div class="header-info">
            <img class="chatbot-logo" src="<?= base_url('assets/images/logo-yapas.png') ?>" alt="Logo Yapas">
            <h2 class="logo-text">Chatbot PPDB</h2>
        </div>
        <button class="material-symbols-rounded" id="close-chatbot" type="button">
            keyboard_arrow_down
        </button>
    </div>

    <!-- chatbot body -->
    <div class="chat-body">
        <div class="message bot-message">
            <img class="bot-avatar" src="<?= base_url('assets/images/logo-yapas.png') ?>" alt="Logo Yapas">
            <div class="message-text">
                Halo!</br>
                Selamat datang di layanan informasi PPDB. Silakan ajukan pertanyaan seputar pendaftaran peserta didik
                baru.
            </div>
        </div>
    </div>

    <div class="chat-footer">
        <form action="#" class="chat-form">
            <textarea class="message-input" id="message-input" required placeholder="Type a message..."></textarea>
            <div class="chat-controls">
                <button class="material-symbols-rounded" id="emoji-picker" type="button">sentiment_satisfied</button>
                <button class="material-symbols-rounded" id="send-message" type="submit">arrow_upward</button>
            </div>
        </form>
    </div>
</div>
