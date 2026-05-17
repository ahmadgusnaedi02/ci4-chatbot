<?php

namespace App\Controllers\Api;

use App\Models\ChatbotIntentModel;
use CodeIgniter\RESTful\ResourceController;

class WhatsAppHistory extends ResourceController
{
    protected $format = 'json';

    private function now(): string
    {
        return (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
    }

    private function timestamp(?string $value = null): string
    {
        if (!$value) {
            return $this->now();
        }

        try {
            return (new \DateTimeImmutable($value))
                ->setTimezone(new \DateTimeZone('Asia/Jakarta'))
                ->format('Y-m-d H:i:s');
        } catch (\Exception) {
            return $this->now();
        }
    }

    private function input(): object
    {
        return $this->request->getJSON() ?? (object) $this->request->getPost();
    }

    private function upsertChat(string $number, ?string $contactName = null): int
    {
        $db = db_connect();
        $chat = $db->table('wa_chats')->where('wa_number', $number)->get()->getRowArray();
        $now = $this->now();

        if ($chat) {
            if ($contactName && empty($chat['contact_name'])) {
                $db->table('wa_chats')->where('id', $chat['id'])->update([
                    'contact_name' => $contactName,
                    'updated_at' => $now,
                ]);
            }

            return (int) $chat['id'];
        }

        $db->table('wa_chats')->insert([
            'wa_number' => $number,
            'contact_name' => $contactName,
            'status' => 'bot',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    private function insertMessage(array $data): int
    {
        $db = db_connect();
        $now = $this->now();
        $sentAt = $this->timestamp($data['sent_at'] ?? null);
        unset($data['sent_at']);

        $db->table('wa_messages')->insert(array_merge([
            'wa_message_id' => null,
            'answered_by_chatbot' => 0,
            'chatbot_understood' => null,
            'needs_cs' => 0,
            'is_training_candidate' => 0,
            'sent_at' => $sentAt,
            'created_at' => $now,
            'updated_at' => $now,
        ], $data));

        $messageId = (int) $db->insertID();

        $db->table('wa_chats')->where('id', $data['chat_id'])->update([
            'last_message' => $data['message'],
            'last_message_at' => $sentAt,
            'updated_at' => $now,
        ]);

        return $messageId;
    }

    public function incoming()
    {
        $payload = $this->input();
        $number = trim((string) ($payload->wa_number ?? ''));
        $message = trim((string) ($payload->message ?? ''));

        if (!$number || !$message) {
            return $this->failValidationErrors('wa_number dan message wajib diisi.');
        }

        $chatId = $this->upsertChat($number, $payload->contact_name ?? null);
        $messageId = $this->insertMessage([
            'chat_id' => $chatId,
            'wa_message_id' => $payload->wa_message_id ?? null,
            'direction' => 'incoming',
            'sender_type' => 'user',
            'message' => $message,
            'sent_at' => $payload->sent_at ?? $this->now(),
        ]);

        return $this->respond([
            'ok' => true,
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);
    }

    public function outgoing()
    {
        $payload = $this->input();
        $message = trim((string) ($payload->message ?? ''));
        $senderType = $payload->sender_type ?? 'bot';
        $chatId = (int) ($payload->chat_id ?? 0);

        if (!$chatId && !empty($payload->wa_number)) {
            $chatId = $this->upsertChat((string) $payload->wa_number, $payload->contact_name ?? null);
        }

        if (!$chatId || !$message || !in_array($senderType, ['bot', 'admin'], true)) {
            return $this->failValidationErrors('chat_id/wa_number, message, dan sender_type valid wajib diisi.');
        }

        $messageId = $this->insertMessage([
            'chat_id' => $chatId,
            'direction' => 'outgoing',
            'sender_type' => $senderType,
            'message' => $message,
            'answered_by_chatbot' => $senderType === 'bot' ? 1 : 0,
            'chatbot_understood' => $payload->chatbot_understood ?? null,
            'needs_cs' => $payload->needs_cs ?? 0,
            'is_training_candidate' => $payload->is_training_candidate ?? 0,
            'sent_at' => $payload->sent_at ?? $this->now(),
        ]);

        if (!empty($payload->user_message_id)) {
            db_connect()->table('wa_messages')->where('id', (int) $payload->user_message_id)->update([
                'answered_by_chatbot' => $senderType === 'bot' ? 1 : 0,
                'chatbot_understood' => $payload->chatbot_understood ?? null,
                'needs_cs' => $payload->needs_cs ?? 0,
                'is_training_candidate' => $payload->is_training_candidate ?? 0,
                'updated_at' => $this->now(),
            ]);
        }

        return $this->respond([
            'ok' => true,
            'message_id' => $messageId,
        ]);
    }

    public function supportChats()
    {
        $db = db_connect();
        $tickets = $db->table('wa_support_tickets t')
            ->select('t.*, c.wa_number, c.contact_name')
            ->join('wa_chats c', 'c.id = t.chat_id')
            ->orderBy('t.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        $result = [];

        foreach ($tickets as $ticket) {
            $messages = $db->table('wa_messages')
                ->where('chat_id', $ticket['chat_id'])
                ->orderBy('id', 'ASC')
                ->get()
                ->getResultArray();

            $result[] = $this->mapTicket($ticket, $messages);
        }

        return $this->respond(['tickets' => $result]);
    }

    public function chats()
    {
        $db = db_connect();
        $keyword = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        $builder = $db->table('wa_chats c')
            ->select('c.*, COUNT(m.id) AS message_count, SUM(m.is_training_candidate = 1) AS training_candidate_count')
            ->join('wa_messages m', 'm.chat_id = c.id', 'left')
            ->groupBy('c.id')
            ->orderBy('c.last_message_at', 'DESC')
            ->orderBy('c.updated_at', 'DESC');

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('c.wa_number', $keyword)
                ->orLike('c.contact_name', $keyword)
                ->orLike('c.last_message', $keyword)
                ->groupEnd();
        }

        if ($status !== '') {
            $builder->where('c.status', $status);
        }

        $chats = array_map(static function (array $chat): array {
            return [
                'id' => (int) $chat['id'],
                'waNumber' => $chat['wa_number'],
                'contactName' => $chat['contact_name'],
                'status' => $chat['status'],
                'lastMessage' => $chat['last_message'],
                'lastMessageAt' => $chat['last_message_at'],
                'messageCount' => (int) $chat['message_count'],
                'trainingCandidateCount' => (int) $chat['training_candidate_count'],
                'createdAt' => $chat['created_at'],
                'updatedAt' => $chat['updated_at'],
            ];
        }, $builder->get()->getResultArray());

        return $this->respond(['chats' => $chats]);
    }

    public function chat($id)
    {
        $db = db_connect();
        $chat = $db->table('wa_chats')->where('id', (int) $id)->get()->getRowArray();

        if (!$chat) {
            return $this->failNotFound('Chat tidak ditemukan.');
        }

        $messages = $db->table('wa_messages')
            ->where('chat_id', (int) $id)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respond([
            'chat' => [
                'id' => (int) $chat['id'],
                'waNumber' => $chat['wa_number'],
                'contactName' => $chat['contact_name'],
                'status' => $chat['status'],
                'lastMessage' => $chat['last_message'],
                'lastMessageAt' => $chat['last_message_at'],
                'createdAt' => $chat['created_at'],
                'updatedAt' => $chat['updated_at'],
                'messages' => $this->mapMessages($messages),
            ],
        ]);
    }

    public function openSupportChatByNumber($number)
    {
        $db = db_connect();
        $ticket = $db->table('wa_support_tickets t')
            ->select('t.*, c.wa_number, c.contact_name')
            ->join('wa_chats c', 'c.id = t.chat_id')
            ->where('c.wa_number', $number)
            ->where('t.status', 'open')
            ->orderBy('t.id', 'DESC')
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->respond(['ticket' => null]);
        }

        $messages = $db->table('wa_messages')
            ->where('chat_id', $ticket['chat_id'])
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respond(['ticket' => $this->mapTicket($ticket, $messages)]);
    }

    public function openSupportChatByChat($chatId)
    {
        $db = db_connect();
        $ticket = $db->table('wa_support_tickets t')
            ->select('t.*, c.wa_number, c.contact_name')
            ->join('wa_chats c', 'c.id = t.chat_id')
            ->where('t.chat_id', (int) $chatId)
            ->where('t.status', 'open')
            ->orderBy('t.id', 'DESC')
            ->get()
            ->getRowArray();

        if (!$ticket) {
            return $this->respond(['ticket' => null]);
        }

        $messages = $db->table('wa_messages')
            ->where('chat_id', $ticket['chat_id'])
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->respond(['ticket' => $this->mapTicket($ticket, $messages)]);
    }

    public function createSupportChat()
    {
        $payload = $this->input();
        $chatId = (int) ($payload->chat_id ?? 0);
        $userMessageId = (int) ($payload->user_message_id ?? 0);

        if (!$chatId) {
            return $this->failValidationErrors('chat_id wajib diisi.');
        }

        $db = db_connect();
        $now = $this->now();
        $open = $db->table('wa_support_tickets')
            ->where('chat_id', $chatId)
            ->where('status', 'open')
            ->get()
            ->getRowArray();

        if ($open) {
            return $this->respond(['ok' => true, 'ticket_id' => (int) $open['id']]);
        }

        $db->table('wa_support_tickets')->insert([
            'chat_id' => $chatId,
            'user_message_id' => $userMessageId ?: null,
            'status' => 'open',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $ticketId = (int) $db->insertID();
        $db->table('wa_chats')->where('id', $chatId)->update([
            'status' => 'waiting_cs',
            'updated_at' => $now,
        ]);

        if ($userMessageId) {
            $db->table('wa_messages')->where('id', $userMessageId)->update([
                'needs_cs' => 1,
                'is_training_candidate' => 1,
                'updated_at' => $now,
            ]);
        }

        return $this->respondCreated([
            'ok' => true,
            'ticket_id' => $ticketId,
        ]);
    }

    public function replySupportChat($id)
    {
        $payload = $this->input();
        $ticketId = (int) $id;
        $message = trim((string) ($payload->message ?? ''));

        if (!$message) {
            return $this->failValidationErrors('message wajib diisi.');
        }

        $db = db_connect();
        $ticket = $db->table('wa_support_tickets')->where('id', $ticketId)->get()->getRowArray();

        if (!$ticket) {
            return $this->failNotFound('Ticket tidak ditemukan.');
        }

        if ($ticket['status'] !== 'open') {
            return $this->failValidationErrors('Chat admin sekolah sudah diakhiri.');
        }

        $adminMessageId = $this->insertMessage([
            'chat_id' => (int) $ticket['chat_id'],
            'direction' => 'outgoing',
            'sender_type' => 'admin',
            'message' => $message,
            'sent_at' => $payload->sent_at ?? $this->now(),
        ]);

        $now = $this->now();
        $db->table('wa_support_tickets')->where('id', $ticketId)->update([
            'admin_reply_message_id' => $adminMessageId,
            'updated_at' => $now,
        ]);
        $db->table('wa_chats')->where('id', (int) $ticket['chat_id'])->update([
            'status' => 'waiting_cs',
            'updated_at' => $now,
        ]);

        return $this->respond([
            'ok' => true,
            'message_id' => $adminMessageId,
        ]);
    }

    public function closeSupportChat($id)
    {
        $ticketId = (int) $id;
        $db = db_connect();
        $ticket = $db->table('wa_support_tickets')->where('id', $ticketId)->get()->getRowArray();

        if (!$ticket) {
            return $this->failNotFound('Ticket tidak ditemukan.');
        }

        $now = $this->now();
        $db->table('wa_support_tickets')->where('id', $ticketId)->update([
            'status' => 'answered',
            'updated_at' => $now,
        ]);
        $db->table('wa_chats')->where('id', (int) $ticket['chat_id'])->update([
            'status' => 'handled_by_cs',
            'updated_at' => $now,
        ]);

        $question = '';
        if (!empty($ticket['user_message_id'])) {
            $source = $db->table('wa_messages')->where('id', (int) $ticket['user_message_id'])->get()->getRowArray();
            $question = $source['message'] ?? '';
        }

        if ($question && !empty($ticket['admin_reply_message_id'])) {
            $adminReply = $db->table('wa_messages')->where('id', (int) $ticket['admin_reply_message_id'])->get()->getRowArray();
            $expectedAnswer = trim((string) ($adminReply['message'] ?? ''));

            if ($expectedAnswer !== '') {
                $db->table('chatbot_training_data')->insert([
                    'source_message_id' => (int) $ticket['user_message_id'],
                    'question' => $question,
                    'expected_answer' => $expectedAnswer,
                    'source' => 'admin_reply',
                    'status' => 'draft',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $intentModel = new ChatbotIntentModel();
                $intentModel->ensureSchema();
                $intentModel->saveIntentDataset([
                    'name' => 'draft_admin_reply_' . $ticketId,
                    'training_phrases' => [$question],
                    'response' => $expectedAnswer,
                    'status' => 'draft',
                    'priority' => 0,
                    'source' => 'admin_reply',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $this->respond([
            'ok' => true,
        ]);
    }

    private function mapTicket(array $ticket, array $messages): array
    {
        return [
            'id' => (string) $ticket['id'],
            'chatId' => (int) $ticket['chat_id'],
            'from' => $ticket['wa_number'],
            'contactName' => $ticket['contact_name'],
            'status' => $ticket['status'] === 'open' ? 'waiting_admin' : $ticket['status'],
            'createdAt' => $ticket['created_at'],
            'updatedAt' => $ticket['updated_at'],
            'messages' => array_map(static function (array $message): array {
                return self::mapMessage($message);
            }, $messages),
        ];
    }

    private function mapMessages(array $messages): array
    {
        return array_map(static function (array $message): array {
            return self::mapMessage($message);
        }, $messages);
    }

    private static function mapMessage(array $message): array
    {
        return [
            'id' => (int) $message['id'],
            'waMessageId' => $message['wa_message_id'],
            'direction' => $message['direction'],
            'sender' => $message['sender_type'],
            'body' => $message['message'],
            'at' => $message['sent_at'] ?? $message['created_at'],
            'answeredByChatbot' => (bool) $message['answered_by_chatbot'],
            'chatbotUnderstood' => $message['chatbot_understood'] === null ? null : (bool) $message['chatbot_understood'],
            'needsCs' => (bool) $message['needs_cs'],
            'isTrainingCandidate' => (bool) $message['is_training_candidate'],
        ];
    }
}
