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


const knowledgeBase = [
    // {
    //     question: 'apa itu ppdb',
    //     answer: 'PPDB adalah proses penerimaan peserta didik baru di sekolah.'
    // },
    {
        question: 'kapan pendaftaran ppdb',
        answer: 'Pendaftaran PPDB dibuka pada bulan Juni setiap tahunnya.'
    },
    {
        question: 'syarat ppdb',
        answer: 'Syarat PPDB adalah fotokopi ijazah, kartu keluarga, dan pas foto.'
    },
    {
        question: 'alamat sekolah',
        answer: 'Alamat sekolah berada di Jl. Contoh No. 10 Kediri.'
    }
];

const normalizeText = (text) =>
    text
        .trim()
        .toLowerCase()
        .replace(/[?!.]+$/, '');

const synonymMap = {
    "dibuka": "pendaftaran",
    "mulai": "pendaftaran",
    "kapan": "kapan"
};

const tokenize = (text) =>
    normalizeText(text)
        .split(/\s+/)
        .filter(Boolean)
        .map((token) => synonymMap[token] ?? token);

function levenshtein(a, b) {
    const matrix = [];

    for (let i = 0; i <= b.length; i++) {
        matrix[i] = [i];
    }

    for (let j = 0; j <= a.length; j++) {
        matrix[0][j] = j;
    }

    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) == a.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1,
                    matrix[i][j - 1] + 1,
                    matrix[i - 1][j] + 1
                );
            }
        }
    }

    return matrix[b.length][a.length];
}

const buildNaiveBayesModel = () => {
    const classes = knowledgeBase.map((item) => {
        const tokens = tokenize(item.question);
        const freqs = {};
        tokens.forEach((t) => (freqs[t] = (freqs[t] || 0) + 1));
        return {
            answer: item.answer,
            tokens,
            freqs,
            totalTokens: tokens.length,
        };
    });

    const vocab = new Set();
    classes.forEach((cls) => cls.tokens.forEach((t) => vocab.add(t)));

    return {
        classes,
        vocabSize: vocab.size || 1,
    };
};

const nbModel = buildNaiveBayesModel();

const classifyNaiveBayes = (text) => {
    const words = tokenize(text);
    if (!words.length) return null;

    let best = { score: -Infinity, answer: null };
    let secondBestScore = -Infinity;

    for (const cls of nbModel.classes) {
        // Uniform prior across all classes
        let score = Math.log(1 / nbModel.classes.length);

        for (const w of words) {
            const count = cls.freqs[w] || 0;
            const likelihood = (count + 1) / (cls.totalTokens + nbModel.vocabSize);
            score += Math.log(likelihood);
        }

        if (score > best.score) {
            secondBestScore = best.score;
            best = { score, answer: cls.answer };
        } else if (score > secondBestScore) {
            secondBestScore = score;
        }
    }

    // Require the best match to be meaningfully better than the second-best
    if (best.answer && best.score - secondBestScore >= 1) {
        return best.answer;
    }

    return null;
};

const keywordMatch = (text) => {
    const queryTokens = tokenize(text).filter((t) => t.length > 2);
    if (!queryTokens.length) return null;

    let best = { score: 0, answer: null };

    for (const item of knowledgeBase) {
        const itemTokens = tokenize(item.question).filter((t) => t.length > 2);
        const matches = itemTokens.filter((t) => queryTokens.includes(t)).length;

        // Score relative to the query (not the item) so shorter queries prioritize exact overlap
        const score = matches / queryTokens.length;

        if (score > best.score) {
            best = { score, answer: item.answer };
        }
    }

    // require at least 50% of query keywords to match
    return best.score >= 0.5 ? best.answer : null;
};

const findBestMatch = (text) => {
    const query = normalizeText(text);

    // 1) Try a strict string similarity check (Levenshtein)
    let best = { score: Infinity, answer: null };
    for (const item of knowledgeBase) {
        const target = normalizeText(item.question);
        const distance = levenshtein(query, target);
        if (distance < best.score) {
            best = { score: distance, answer: item.answer };
        }
    }

    const maxDistance = Math.max(1, Math.floor(query.length * 0.4));
    if (best.answer && best.score <= maxDistance) {
        return best.answer;
    }

    // 2) Fallback: Naive Bayes classification (handles extra/missing words better)
    const nbAnswer = classifyNaiveBayes(text);
    if (nbAnswer) return nbAnswer;

    // 3) Fallback: keyword overlap (simple heuristic to catch queries like "alamat sekolahnya dimana")
    return keywordMatch(text);
};

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
          <svg class="bot-avatar" xmlns="http://www.w3.org/2000/svg" width="50" height="50" viewBox="0 0 1024 1024">
            <path d="M738.3 287.6H285.7c-59 0-106.8 47.8-106.8 106.8v303.1c0 59 47.8 106.8 106.8 106.8h81.5v111.1c0 .7.8 1.1 1.4.7l166.9-110.6 41.8-.8h117.4l43.6-.4c59 0 106.8-47.8 106.8-106.8V394.5c0-59-47.8-106.9-106.8-106.9zM351.7 448.2c0-29.5 23.9-53.5 53.5-53.5s53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5-53.5-23.9-53.5-53.5zm157.9 267.1c-67.8 0-123.8-47.5-132.3-109h264.6c-8.6 61.5-64.5 109-132.3 109zm110-213.7c-29.5 0-53.5-23.9-53.5-53.5s23.9-53.5 53.5-53.5 53.5 23.9 53.5 53.5-23.9 53.5-53.5 53.5zM867.2 644.5V453.1h26.5c19.4 0 35.1 15.7 35.1 35.1v121.1c0 19.4-15.7 35.1-35.1 35.1h-26.5zM95.2 609.4V488.2c0-19.4 15.7-35.1 35.1-35.1h26.5v191.3h-26.5c-19.4 0-35.1-15.7-35.1-35.1zM561.5 149.6c0 23.4-15.6 43.3-36.9 49.7v44.9h-30v-44.9c-21.4-6.5-36.9-26.3-36.9-49.7 0-28.6 23.3-51.9 51.9-51.9s51.9 23.3 51.9 51.9z"></path>
          </svg>
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
