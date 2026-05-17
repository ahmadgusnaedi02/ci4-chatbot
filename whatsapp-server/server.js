const cors = require('cors');
const express = require('express');
const fs = require('fs');
const http = require('http');
const QRCode = require('qrcode');
const { Client, LocalAuth } = require('whatsapp-web.js');
const { Server } = require('socket.io');

const PORT = process.env.PORT || 3001;
const CHATBOT_URL = process.env.CHATBOT_URL || 'http://ci4-chatbot.test/chatbot';
const WA_API_URL = process.env.WA_API_URL || 'http://ci4-chatbot.test/api/wa';
const STORE_PATH = './data/support-chats.json';
const app = express();
const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST'],
  },
});

app.use(cors());
app.use(express.json());

let client;
let latestQr = null;
let status = 'loading';
let connectedNumber = null;
let isStarting = false;
let supportStore = loadSupportStore();

const chromePath = [
  process.env.CHROME_PATH,
  'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
  'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
].find((path) => path && fs.existsSync(path));

function emitStatus(socket = io) {
  socket.emit('wa:status', {
    status,
    number: connectedNumber,
  });

  if (latestQr && status === 'qr') {
    socket.emit('wa:qr', { image: latestQr });
  }
}

function log(message) {
  console.log(`[wa-server] ${message}`);
}

function loadSupportStore() {
  try {
    if (!fs.existsSync(STORE_PATH)) {
      return { tickets: [], sessions: {} };
    }

    return JSON.parse(fs.readFileSync(STORE_PATH, 'utf8'));
  } catch (error) {
    console.error(`[wa-server] Failed to load support store: ${error.message}`);
    return { tickets: [], sessions: {} };
  }
}

function saveSupportStore() {
  fs.mkdirSync('./data', { recursive: true });
  fs.writeFileSync(STORE_PATH, JSON.stringify(supportStore, null, 2));
}

function nowIso() {
  return new Date().toISOString();
}

function normalizeText(text) {
  return String(text || '').toLowerCase().trim();
}

function wantsCustomerService(text) {
  const normalized = normalizeText(text);

  return [
    'admin sekolah',
    'admin',
    'terhubung',
    'hubungkan',
    'operator',
    'iya',
    'ya',
    'mau',
  ].some((keyword) => normalized.includes(keyword));
}

function isCustomerServiceOffer(text) {
  const normalized = normalizeText(text);
  return normalized.includes('terhubung dengan admin sekolah')
    || normalized.includes('terhubung dengan admin');
}

async function apiRequest(path, options = {}) {
  const response = await fetch(`${WA_API_URL}${path}`, {
    headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
    ...options,
  });
  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    throw new Error(data.message || data.messages?.error || `Request API gagal: ${response.status}`);
  }

  return data;
}

async function getOpenTicket(chatId) {
  const data = await apiRequest(`/support-chats/open-chat/${chatId}`);
  return data.ticket;
}

async function logIncomingMessage(message) {
  const contact = await message.getContact().catch(() => null);

  return apiRequest('/messages/incoming', {
    method: 'POST',
    body: JSON.stringify({
      wa_number: message.from,
      contact_name: contact?.pushname || contact?.name || null,
      wa_message_id: message.id?._serialized || null,
      message: message.body,
      sent_at: nowIso(),
    }),
  });
}

async function logOutgoingMessage(payload) {
  return apiRequest('/messages/outgoing', {
    method: 'POST',
    body: JSON.stringify({
      sent_at: nowIso(),
      ...payload,
    }),
  });
}

async function createSupportTicket(chatId, userMessageId) {
  const data = await apiRequest('/support-chats', {
    method: 'POST',
    body: JSON.stringify({
      chat_id: chatId,
      user_message_id: userMessageId,
    }),
  });
  io.emit('support:changed', data);

  return data;
}

function createClient() {
  return new Client({
    authStrategy: new LocalAuth({
      clientId: 'ci4-chatbot',
      dataPath: './sessions',
    }),
    puppeteer: {
      headless: true,
      executablePath: chromePath,
      args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
      ],
    },
  });
}

async function startClient() {
  if (isStarting) {
    return;
  }

  isStarting = true;
  latestQr = null;
  status = 'loading';
  connectedNumber = null;
  emitStatus();
  log('Starting WhatsApp client');

  client = createClient();

  const loadingTimer = setTimeout(() => {
    if (status === 'loading') {
      io.emit('wa:error', {
        message: 'WhatsApp Web masih loading. Cek koneksi internet, Chrome, atau coba klik Refresh.',
      });
      log('WhatsApp Web is still loading after 60 seconds');
    }
  }, 60000);

  client.on('qr', async (qr) => {
    clearTimeout(loadingTimer);
    latestQr = await QRCode.toDataURL(qr, {
      margin: 1,
      scale: 8,
      width: 320,
    });
    status = 'qr';
    log('QR received');
    emitStatus();
    io.emit('wa:qr', { image: latestQr });
  });

  client.on('ready', () => {
    clearTimeout(loadingTimer);
    latestQr = null;
    status = 'ready';
    connectedNumber = client.info?.wid?.user || null;
    log(`WhatsApp client ready${connectedNumber ? `: ${connectedNumber}` : ''}`);
    emitStatus();
  });

  client.on('authenticated', () => {
    status = 'authenticated';
    log('WhatsApp authenticated');
    emitStatus();
  });

  client.on('auth_failure', (message) => {
    clearTimeout(loadingTimer);
    status = 'auth_failure';
    log(`Authentication failed: ${message}`);
    io.emit('wa:error', { message: `Autentikasi gagal: ${message}` });
    emitStatus();
  });

  client.on('disconnected', (reason) => {
    clearTimeout(loadingTimer);
    latestQr = null;
    status = 'disconnected';
    connectedNumber = null;
    log(`WhatsApp disconnected: ${reason}`);
    io.emit('wa:error', { message: `WhatsApp terputus: ${reason}` });
    emitStatus();
  });

  client.on('message', async (message) => {
    if (message.fromMe || message.from.endsWith('@g.us')) {
      return;
    }

    try {
      const incoming = await logIncomingMessage(message);
      const openTicket = await getOpenTicket(incoming.chat_id);

      if (openTicket) {
        const reply = 'Pesan tambahan Anda sudah diteruskan ke admin sekolah. Mohon tunggu balasan admin.';
        await message.reply(reply);
        await logOutgoingMessage({
          chat_id: incoming.chat_id,
          sender_type: 'bot',
          message: reply,
          user_message_id: incoming.message_id,
          chatbot_understood: 0,
          needs_cs: 1,
          is_training_candidate: 1,
        });
        io.emit('support:changed', openTicket);
        log(`Forwarded message to open ticket ${openTicket.id}`);
        return;
      }

      const session = supportStore.sessions[message.from] || {};

      if (session.handoffOffered && wantsCustomerService(message.body)) {
        await createSupportTicket(
          session.chatId || incoming.chat_id,
          session.lastUserMessageId || incoming.message_id
        );

        supportStore.sessions[message.from] = {
          handoffOffered: false,
          waitingAdmin: true,
        };
        saveSupportStore();

        const reply = 'Baik, Anda sudah terhubung dengan admin sekolah. Mohon tunggu balasan admin.';
        await message.reply(reply);
        await logOutgoingMessage({
          chat_id: incoming.chat_id,
          sender_type: 'bot',
          message: reply,
          user_message_id: incoming.message_id,
          chatbot_understood: 0,
          needs_cs: 1,
          is_training_candidate: 1,
        });
        log(`Created support ticket for ${message.from}`);
        return;
      }

      const response = await fetch(CHATBOT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: message.body }),
      });
      const data = await response.json();
      const reply = data?.choices?.[0]?.message?.content || 'Maaf, chatbot belum bisa menjawab pesan ini.';
      const understood = !isCustomerServiceOffer(reply);

      await message.reply(reply);
      await logOutgoingMessage({
        chat_id: incoming.chat_id,
        sender_type: 'bot',
        message: reply,
        user_message_id: incoming.message_id,
        chatbot_understood: understood ? 1 : 0,
        needs_cs: understood ? 0 : 1,
        is_training_candidate: understood ? 0 : 1,
      });
      supportStore.sessions[message.from] = {
        handoffOffered: !understood,
        chatId: incoming.chat_id,
        lastUserMessageId: incoming.message_id,
        lastUserMessage: message.body,
        lastUserAt: nowIso(),
        lastBotMessage: reply,
        lastBotAt: nowIso(),
      };
      saveSupportStore();
      log(`Replied to ${message.from}`);
    } catch (error) {
      log(`Chatbot request failed: ${error.message}`);
      io.emit('wa:error', { message: `Chatbot error: ${error.message}` });
    }
  });

  try {
    await client.initialize();
  } catch (error) {
    clearTimeout(loadingTimer);
    status = 'error';
    log(`Initialize error: ${error.message}`);
    io.emit('wa:error', { message: error.message });
    emitStatus();
  } finally {
    isStarting = false;
  }
}

async function destroyClient() {
  if (!client) {
    return;
  }

  try {
    await client.destroy();
  } catch (error) {
    io.emit('wa:error', { message: error.message });
  }

  client = null;
}

app.get('/api/status', (req, res) => {
  res.json({
    status,
    number: connectedNumber,
    hasQr: Boolean(latestQr),
  });
});

app.post('/api/restart', async (req, res) => {
  await destroyClient();
  await startClient();
  res.json({ ok: true });
});

app.post('/api/logout', async (req, res) => {
  if (client) {
    try {
      await client.logout();
    } catch (error) {
      io.emit('wa:error', { message: error.message });
    }
  }

  await destroyClient();
  latestQr = null;
  status = 'loading';
  connectedNumber = null;
  await startClient();
  res.json({ ok: true });
});

app.get('/api/support-chats', (req, res) => {
  apiRequest('/support-chats')
    .then((data) => res.json(data))
    .catch((error) => res.status(500).json({ message: error.message }));
});

app.get('/api/support-chats/:id', (req, res) => {
  apiRequest('/support-chats')
    .then((data) => {
      const ticket = (data.tickets || []).find((item) => item.id === req.params.id);

      if (!ticket) {
        res.status(404).json({ message: 'Ticket tidak ditemukan.' });
        return;
      }

      res.json({ ticket });
    })
    .catch((error) => res.status(500).json({ message: error.message }));
});

app.post('/api/support-chats/:id/reply', async (req, res) => {
  const text = String(req.body.message || '').trim();
  let ticket;

  try {
    const ticketData = await apiRequest('/support-chats');
    ticket = (ticketData.tickets || []).find((item) => item.id === req.params.id);
  } catch (error) {
    res.status(500).json({ message: error.message });
    return;
  }

  if (!text) {
    res.status(422).json({ message: 'Pesan balasan wajib diisi.' });
    return;
  }

  if (!ticket) {
    res.status(404).json({ message: 'Ticket tidak ditemukan.' });
    return;
  }

  const isWebTicket = String(ticket.from || '').startsWith('WEB-');

  if (!isWebTicket && (!client || status !== 'ready')) {
    res.status(503).json({ message: 'WhatsApp belum terhubung.' });
    return;
  }

  try {
    if (!isWebTicket) {
      await client.sendMessage(ticket.from, text);
    }

    await apiRequest(`/support-chats/${encodeURIComponent(ticket.id)}/reply`, {
      method: 'POST',
      body: JSON.stringify({ message: text, sent_at: nowIso() }),
    });

    if (!isWebTicket) {
      supportStore.sessions[ticket.from] = {
        handoffOffered: false,
        waitingAdmin: true,
      };
      saveSupportStore();
    }

    io.emit('support:changed', ticket);
    res.json({ ok: true, ticket });
  } catch (error) {
    log(`Admin reply failed: ${error.message}`);
    res.status(500).json({ message: error.message });
  }
});

app.post('/api/support-chats/:id/end', async (req, res) => {
  let ticket;

  try {
    const ticketData = await apiRequest('/support-chats');
    ticket = (ticketData.tickets || []).find((item) => item.id === req.params.id);
  } catch (error) {
    res.status(500).json({ message: error.message });
    return;
  }

  if (!ticket) {
    res.status(404).json({ message: 'Ticket tidak ditemukan.' });
    return;
  }

  try {
    await apiRequest(`/support-chats/${encodeURIComponent(ticket.id)}/end`, {
      method: 'POST',
      body: JSON.stringify({ ended_at: nowIso() }),
    });
    supportStore.sessions[ticket.from] = {
      handoffOffered: false,
      waitingAdmin: false,
    };
    saveSupportStore();
    io.emit('support:changed', ticket);
    res.json({ ok: true, ticket });
  } catch (error) {
    log(`End support chat failed: ${error.message}`);
    res.status(500).json({ message: error.message });
  }
});

io.on('connection', (socket) => {
  emitStatus(socket);
  apiRequest('/support-chats')
    .then((data) => socket.emit('support:list', data))
    .catch((error) => socket.emit('wa:error', { message: error.message }));
});

server.listen(PORT, () => {
  console.log(`WhatsApp server running on http://localhost:${PORT}`);
  startClient();
});
