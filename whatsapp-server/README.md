# WhatsApp Server

Service Node.js ini menghasilkan QR WhatsApp Web untuk halaman CodeIgniter:

```bash
cd whatsapp-server
npm install
npm start
```

Setelah server berjalan, buka:

```text
http://ci4-chatbot.test/dashboard/scan-whatsapp
```

Default port service adalah `3001`. Jika ingin mengganti:

```bash
set PORT=3002
npm start
```

Session WhatsApp disimpan di folder `sessions`, jadi tidak perlu scan ulang selama session masih valid.

Pesan WhatsApp yang masuk akan diteruskan ke endpoint chatbot CodeIgniter:

```text
http://ci4-chatbot.test/chatbot
```

Jika domain lokalnya berbeda, jalankan dengan environment variable:

```bash
set CHATBOT_URL=http://domain-kamu.test/chatbot
npm start
```

Alur handoff CS:

1. Chatbot menjawab pesan masuk.
2. Jika chatbot tidak yakin, chatbot menawarkan: "Apakah Anda ingin terhubung dengan CS?"
3. Jika user membalas "ya", "cs", "admin", atau "terhubung", pesan masuk ke antrian admin.
4. Admin membalas lewat halaman:

```text
http://ci4-chatbot.test/dashboard/support-chat
```
