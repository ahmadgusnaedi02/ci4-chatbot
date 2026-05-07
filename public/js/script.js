const chatBody = document.querySelector('.chat-body');
const messageInput = document.querySelector('.message-input');
const sendMessageButton = document.querySelector('#send-message');
const chatbotToggler = document.querySelector('#chatbot-toggler');
// Track base textarea height to reset when input is emptied
const baseInputHeight = messageInput.scrollHeight;
const baseUrlMeta = document.querySelector('meta[name="app-base-url"]');
const appBaseUrl = baseUrlMeta ? baseUrlMeta.getAttribute('content') : `${window.location.origin}/`;
const chatbotUrl = new URL('chatbot', appBaseUrl).toString();
const webChatSessionKey = 'school_web_chat_session_id';
const botLogo = `${appBaseUrl}assets/images/logo-yapas.png`;

const getWebChatSessionId = () => {
    let sessionId = localStorage.getItem(webChatSessionKey);

    if (!sessionId) {
        const randomPart = window.crypto?.randomUUID ? window.crypto.randomUUID() : `${Date.now()}-${Math.random()}`;
        sessionId = `web-${randomPart}`;
        localStorage.setItem(webChatSessionKey, sessionId);
    }

    return sessionId;
};

const userData = {
    message: 'null'
}

const createMessageElement = (content, ...classess) => {
    const div = document.createElement('div');
    div.classList.add('message', ...classess);
    div.innerHTML = content;
    return div;
}

// escape HTML to prevent injection (e.g. <h2>hi</h2>)
const escapeHtml = (str) => {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Remove Markdown-like markers so responses don't show **bold** literally
const cleanFormattingMarkers = (str) => {
    return String(str)
        .replace(/\*\*/g, '')
        .replace(/__/g, '')
        .replace(/`/g, '');
}
const generateBotResponse = async (botMessageDiv) => {

    const messageElement = botMessageDiv.querySelector('.message-text');

    try {
        const response = await fetch(chatbotUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                source: 'web',
                web_session_id: getWebChatSessionId(),
                contact_name: 'Pengunjung Website',
                message: userData.message,
            }),
        });
        const data = await response.json();
        const reply = data?.choices?.[0]?.message?.content || 'Maaf, saya belum bisa menjawab pertanyaan itu.';
        messageElement.innerText = cleanFormattingMarkers(reply);
    } catch (error) {
        messageElement.innerText = 'Maaf, koneksi chatbot sedang bermasalah. Silakan coba lagi.';
    }

    chatBody.scrollTop = chatBody.scrollHeight;

    botMessageDiv.classList.remove('thinking');
};


// handle outgoing user message
const handleOutgoingMessage = (e) => {
    if (e) e.preventDefault();
    const text = messageInput.value.trim();
    if (!text) return; // nothing to send

    userData.message = text;
    // escape any HTML tags so they won't be rendered
    const safeText = escapeHtml(text);
    const messageContent = '<div class="message-text">' + safeText + '</div>';
    const outgoingMessageDiv = createMessageElement(messageContent, 'user-message');
    chatBody.appendChild(outgoingMessageDiv);

    // reset input and scroll
    messageInput.value = '';
    messageInput.style.height = `${baseInputHeight}px`;
    messageInput.focus();
    chatBody.scrollTop = chatBody.scrollHeight;

    // close emoji picker (so it doesn't stay open after sending)
    document.body.classList.remove('show-emoji-picker');

    setTimeout(() => {
        // simulate bot response after a short delay

        const botMessageContent = `
        <img class="chatbot-logo" src="${botLogo}" alt="Logo Yafas">
        <div class="message-text">
            <div class="thinking-indicator">
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
        </div>
        `;
        const botMessageDiv = createMessageElement(botMessageContent, 'bot-message', 'thinking');
        chatBody.appendChild(botMessageDiv);
        chatBody.scrollTop = chatBody.scrollHeight;
        generateBotResponse(botMessageDiv);
    }, 600);
}
// handler  for pressing Enter key in the message input
messageInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter") {
        handleOutgoingMessage(e);
    }
});


messageInput.addEventListener('input', () => {
    // Reset to base height first so shrinking works when text is cleared
    messageInput.style.height = `${baseInputHeight}px`;

    // If there is still content, expand to fit it
    if (messageInput.value.trim().length) {
        messageInput.style.height = `${messageInput.scrollHeight}px`;
    }
});



const emojiButton = document.querySelector('#emoji-picker');

const picker = new EmojiMart.Picker({
    theme: 'light',
    skinTonePosition: 'none',
    previewPosition: 'none',
    onEmojiSelect: (emoji) => {
        // Insert selected emoji into the message input at the cursor position
        const { selectionStart: start, selectionEnd: end } = messageInput;
        messageInput.setRangeText(emoji.native, start, end, 'end');
        messageInput.focus();
    },
    onclickOutside: (e) => {
        // Close picker when clicking outside of it (but keep it open when clicking the emoji button itself)
        if (e.target.closest('#emoji-picker')) {
            document.body.classList.toggle('show-emoji-picker');
        } else {
            document.body.classList.remove('show-emoji-picker');
        }
    }
});

document.querySelector('.chat-form').appendChild(picker);

emojiButton.addEventListener('click', (e) => {
    e.preventDefault();
    document.body.classList.toggle('show-emoji-picker');
});

sendMessageButton.addEventListener("click", (e) => {
    handleOutgoingMessage(e);
});

chatbotToggler.addEventListener('click', () => {
    document.body.classList.toggle('show-chatbot');
});

const closeChatbotBtn = document.querySelector('#close-chatbot');
if (closeChatbotBtn) {
    closeChatbotBtn.addEventListener('click', () => {
        document.body.classList.remove('show-chatbot');
    });
}
