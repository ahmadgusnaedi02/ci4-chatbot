<?php

namespace App\Controllers;

use App\Models\ChatbotKnowledgeModel;
use CodeIgniter\RESTful\ResourceController;

class Chatbot extends ResourceController
{
    protected $format = 'json';

    private array $synonymMap = [
        'dibuka' => 'pendaftaran',
        'mulai' => 'pendaftaran',
        'persyaratan' => 'syarat',
        'syaratnya' => 'syarat',
        'daftar' => 'pendaftaran',
        'mendaftar' => 'pendaftaran',
        'lokasi' => 'alamat',
        'dimana' => 'alamat',
        'mana' => 'alamat',
        'sekolahnya' => 'sekolah',
    ];

    private array $stopWords = [
        'apa',
        'aja',
        'saja',
        'yang',
        'untuk',
        'di',
        'ke',
        'itu',
        'ini',
    ];

    private function normalizeText(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[?!.]+$/', '', $text);
        $text = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $text);

        return trim(preg_replace('/\s+/', ' ', $text));
    }

    private function tokenize(string $text): array
    {
        $tokens = preg_split('/\s+/', $this->normalizeText($text), -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_map(function ($token) {
            if (isset($this->synonymMap[$token])) {
                return $this->synonymMap[$token];
            }

            if (strlen($token) > 5 && str_ends_with($token, 'nya')) {
                $token = substr($token, 0, -3);
            }

            return $this->synonymMap[$token] ?? $token;
        }, $tokens ?: []);

        return array_values(array_filter($tokens, fn ($token) => !in_array($token, $this->stopWords, true)));
    }

    private function findLocalAnswer(string $message): ?string
    {
        $query = $this->normalizeText($message);

        if ($query === '') {
            return null;
        }

        $knowledgeBase = (new ChatbotKnowledgeModel())->getActiveKnowledge();
        if (!$knowledgeBase) {
            return null;
        }

        $best = [
            'distance' => PHP_INT_MAX,
            'response' => null,
        ];

        foreach ($knowledgeBase as $item) {
            $target = $this->normalizeText((string) $item['pertanyaan']);
            $distance = levenshtein($query, $target);

            if ($distance < $best['distance']) {
                $best = [
                    'distance' => $distance,
                    'response' => $item['response'],
                ];
            }
        }

        $maxDistance = max(1, (int) floor(strlen($query) * 0.4));
        if ($best['response'] && $best['distance'] <= $maxDistance) {
            return $best['response'];
        }

        $queryTokens = array_values(array_filter($this->tokenize($message), fn ($token) => strlen($token) > 2));
        if (!$queryTokens) {
            return null;
        }

        $bestScore = 0;
        $bestResponse = null;

        foreach ($knowledgeBase as $item) {
            $searchText = implode(' ', array_filter([
                (string) ($item['pertanyaan'] ?? ''),
                (string) ($item['intent'] ?? ''),
                (string) ($item['keyword'] ?? ''),
            ]));
            $itemTokens = array_values(array_filter($this->tokenize($searchText), fn ($token) => strlen($token) > 2));
            if (!$itemTokens) {
                continue;
            }

            $matches = count(array_intersect($itemTokens, $queryTokens));
            $score = ($matches / count($queryTokens)) + ($matches / max(1, count($itemTokens)));

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestResponse = $item['response'];
            }
        }

        return $bestScore >= 1 ? $bestResponse : null;
    }

    private function choiceResponse(string $content): array
    {
        return [
            'choices' => [
                [
                    'message' => [
                        'content' => $content,
                    ],
                ],
            ],
        ];
    }

    private function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    private function upsertWebChat(string $sessionId, ?string $contactName = null): int
    {
        $db = db_connect();
        $number = 'WEB-' . substr(hash('sha256', $sessionId), 0, 16);
        $chat = $db->table('wa_chats')->where('wa_number', $number)->get()->getRowArray();
        $now = $this->now();

        if ($chat) {
            return (int) $chat['id'];
        }

        $db->table('wa_chats')->insert([
            'wa_number' => $number,
            'contact_name' => $contactName ?: 'Pengunjung Website',
            'status' => 'bot',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return (int) $db->insertID();
    }

    private function insertMessage(int $chatId, string $direction, string $senderType, string $message, array $flags = []): int
    {
        $db = db_connect();
        $now = $this->now();

        $db->table('wa_messages')->insert(array_merge([
            'chat_id' => $chatId,
            'wa_message_id' => null,
            'direction' => $direction,
            'sender_type' => $senderType,
            'message' => $message,
            'answered_by_chatbot' => $senderType === 'bot' ? 1 : 0,
            'chatbot_understood' => null,
            'needs_cs' => 0,
            'is_training_candidate' => 0,
            'sent_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $flags));

        $messageId = (int) $db->insertID();

        $db->table('wa_chats')->where('id', $chatId)->update([
            'last_message' => $message,
            'last_message_at' => $now,
            'updated_at' => $now,
        ]);

        return $messageId;
    }

    private function isCustomerServiceOffer(string $text): bool
    {
        return str_contains($this->normalizeText($text), 'terhubung dengan cs');
    }

    public function chat()
    {
        $apiKey = getenv('GROQ_API_KEY');

        $input = $this->request->getJSON();
        $message = trim((string) ($input->message ?? ''));
        $source = strtolower(trim((string) ($input->source ?? '')));
        $isWebChat = $source === 'web';
        $chatId = null;
        $userMessageId = null;

        if ($message === '') {
            return $this->respond($this->choiceResponse('Maaf, pesan Anda kosong.'));
        }

        if ($isWebChat) {
            $webSessionId = trim((string) ($input->web_session_id ?? session_id()));
            $chatId = $this->upsertWebChat($webSessionId ?: session_id(), $input->contact_name ?? null);
            $userMessageId = $this->insertMessage($chatId, 'incoming', 'user', $message, [
                'answered_by_chatbot' => 0,
            ]);
        }

        $localAnswer = $this->findLocalAnswer($message);
        if ($localAnswer) {
            if ($isWebChat && $chatId) {
                $this->insertMessage($chatId, 'outgoing', 'bot', $localAnswer, [
                    'chatbot_understood' => 1,
                ]);
                db_connect()->table('wa_messages')->where('id', $userMessageId)->update([
                    'answered_by_chatbot' => 1,
                    'chatbot_understood' => 1,
                    'updated_at' => $this->now(),
                ]);
            }

            return $this->respond(array_merge($this->choiceResponse($localAnswer), [
                'chat_id' => $chatId,
            ]));
        }

        if (!$apiKey) {
            $fallback = 'Maaf, saya belum bisa memahami pertanyaan Anda. Apakah Anda ingin terhubung dengan CS?';

            if ($isWebChat && $chatId) {
                $this->insertMessage($chatId, 'outgoing', 'bot', $fallback, [
                    'chatbot_understood' => 0,
                    'needs_cs' => 1,
                    'is_training_candidate' => 1,
                ]);
                db_connect()->table('wa_messages')->where('id', $userMessageId)->update([
                    'answered_by_chatbot' => 1,
                    'chatbot_understood' => 0,
                    'needs_cs' => 1,
                    'is_training_candidate' => 1,
                    'updated_at' => $this->now(),
                ]);
            }

            return $this->respond(array_merge($this->choiceResponse($fallback), [
                'chat_id' => $chatId,
            ]));
        }

        $client = \Config\Services::curlrequest();

        $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Anda adalah chatbot customer service berbahasa Indonesia. Jawab dengan singkat, jelas, dan sopan. Jika pertanyaan tidak bisa dijawab dengan yakin, jangan mengarang. Jawab persis dengan pola: "Maaf, saya belum bisa memahami pertanyaan Anda. Apakah Anda ingin terhubung dengan CS?"'
                    ],
                    ['role' => 'user', 'content' => $message]
                ],
            ]
        ]);

        $body = json_decode($response->getBody());
        $reply = trim((string) ($body->choices[0]->message->content ?? 'Maaf, chatbot belum bisa menjawab pesan ini.'));

        if ($isWebChat && $chatId) {
            $understood = !$this->isCustomerServiceOffer($reply);
            $this->insertMessage($chatId, 'outgoing', 'bot', $reply, [
                'chatbot_understood' => $understood ? 1 : 0,
                'needs_cs' => $understood ? 0 : 1,
                'is_training_candidate' => $understood ? 0 : 1,
            ]);
            db_connect()->table('wa_messages')->where('id', $userMessageId)->update([
                'answered_by_chatbot' => 1,
                'chatbot_understood' => $understood ? 1 : 0,
                'needs_cs' => $understood ? 0 : 1,
                'is_training_candidate' => $understood ? 0 : 1,
                'updated_at' => $this->now(),
            ]);
        }

        return $this->respond(array_merge((array) $body, [
            'chat_id' => $chatId,
        ]));
    }
}
