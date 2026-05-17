<?php

namespace App\Controllers;

use App\Models\ChatbotIntentModel;
use CodeIgniter\RESTful\ResourceController;

class Chatbot extends ResourceController
{
    protected $format = 'json';

    private ?array $nlpRules = null;

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
        $tokens = array_map(fn ($token) => $this->normalizeToken($token), $tokens ?: []);
        $rules = $this->getNlpRules();

        return array_values(array_filter($tokens, fn ($token) => !in_array($token, $rules['stopWords'], true)));
    }

    private function normalizeToken(string $token): string
    {
        $rules = $this->getNlpRules();

        if (isset($rules['synonyms'][$token])) {
            return $rules['synonyms'][$token];
        }

        foreach ($rules['suffixes'] as $suffix) {
            if (strlen($token) > strlen($suffix) + 3 && str_ends_with($token, $suffix)) {
                $token = substr($token, 0, -strlen($suffix));
                break;
            }
        }

        return $rules['synonyms'][$token] ?? $token;
    }

    private function getNlpRules(): array
    {
        if ($this->nlpRules === null) {
            $this->nlpRules = (new ChatbotIntentModel())->getNlpRules();
        }

        return $this->nlpRules;
    }

    private function isGreetingOnly(string $message): bool
    {
        $normalized = $this->normalizeText($message);

        if ($normalized === '') {
            return false;
        }

        $greetingPhrases = [
            'assalamualaikum',
            'assalamu alaikum',
            'asalamualaikum',
            'halo',
            'hallo',
            'hai',
            'hy',
            'selamat pagi',
            'selamat siang',
            'selamat sore',
            'selamat malam',
            'pagi',
            'siang',
            'sore',
            'malam',
            'permisi',
        ];

        if (in_array($normalized, $greetingPhrases, true)) {
            return true;
        }

        $tokens = $this->tokenize($message);
        $greetingTokens = [
            'assalamualaikum',
            'halo',
            'hai',
            'hy',
            'selamat',
            'pagi',
            'siang',
            'sore',
            'malam',
            'permisi',
            'admin',
            'bu',
            'pak',
        ];

        return $tokens !== [] && count(array_diff($tokens, $greetingTokens)) === 0;
    }

    private function getIntentResponse(array $dataset, string $intentName): ?string
    {
        foreach ($dataset as $item) {
            if (($item['name'] ?? '') === $intentName && !empty($item['response'])) {
                return $item['response'];
            }
        }

        return null;
    }

    private function findLocalAnswer(string $message): ?string
    {
        $query = $this->normalizeText($message);

        if ($query === '') {
            return null;
        }

        $dataset = (new ChatbotIntentModel())->getActiveTrainingDataset();
        if (!$dataset) {
            return null;
        }

        if ($this->isGreetingOnly($message)) {
            return $this->getIntentResponse($dataset, 'salam_pembuka');
        }

        return $this->findNaiveBayesAnswer($message, $dataset);
    }

    private function findNaiveBayesAnswer(string $message, array $dataset): ?string
    {
        $queryTokens = array_values(array_filter($this->tokenize($message), fn ($token) => strlen($token) > 2));
        if (!$queryTokens) {
            return null;
        }

        $classes = [];
        $vocabulary = [];
        $totalDocuments = 0;

        foreach ($dataset as $item) {
            $intent = trim((string) ($item['name'] ?? ''));
            if ($intent === '') {
                continue;
            }

            $trainingText = implode(' ', $item['training_phrases'] ?? []);
            $tokens = array_values(array_filter($this->tokenize($trainingText), fn ($token) => strlen($token) > 2));
            if (!$tokens) {
                continue;
            }

            if (!isset($classes[$intent])) {
                $classes[$intent] = [
                    'documentCount' => 0,
                    'tokenCount' => 0,
                    'tokens' => [],
                    'bestResponse' => null,
                    'bestPriority' => PHP_INT_MIN,
                ];
            }

            $classes[$intent]['documentCount']++;
            $totalDocuments++;

            foreach ($tokens as $token) {
                $classes[$intent]['tokens'][$token] = ($classes[$intent]['tokens'][$token] ?? 0) + 1;
                $classes[$intent]['tokenCount']++;
                $vocabulary[$token] = true;
            }

            $priority = (int) ($item['priority'] ?? 0);
            if ($priority > $classes[$intent]['bestPriority']) {
                $classes[$intent]['bestPriority'] = $priority;
                $classes[$intent]['bestResponse'] = $item['response'] ?? null;
            }
        }

        if (!$classes || $totalDocuments === 0 || !$vocabulary) {
            return null;
        }

        $vocabularySize = count($vocabulary);
        $bestIntent = null;
        $bestScore = null;
        $bestMatchedTokens = 0;

        foreach ($classes as $intent => $classData) {
            $score = log($classData['documentCount'] / $totalDocuments);
            $matchedTokens = 0;

            foreach ($queryTokens as $token) {
                $tokenFrequency = $classData['tokens'][$token] ?? 0;
                if ($tokenFrequency > 0) {
                    $matchedTokens++;
                }

                $score += log(($tokenFrequency + 1) / ($classData['tokenCount'] + $vocabularySize));
            }

            if ($bestScore === null || $score > $bestScore) {
                $bestScore = $score;
                $bestIntent = $intent;
                $bestMatchedTokens = $matchedTokens;
            }
        }

        if ($bestIntent === null || $bestMatchedTokens === 0) {
            return null;
        }

        return $classes[$bestIntent]['bestResponse'] ?: null;
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
        return (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d H:i:s');
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
        $normalized = $this->normalizeText($text);

        return str_contains($normalized, 'terhubung dengan admin sekolah')
            || str_contains($normalized, 'terhubung dengan admin');
    }

    private function wantsCustomerService(string $text): bool
    {
        $normalized = $this->normalizeText($text);
        $tokens = $this->tokenize($text);

        foreach ([
            'admin sekolah',
            'terhubung dengan admin sekolah',
            'terhubung dengan admin',
            'hubungkan ke admin sekolah',
            'hubungkan ke admin',
        ] as $phrase) {
            if (str_contains($normalized, $phrase)) {
                return true;
            }
        }

        return (bool) array_intersect($tokens, [
            'admin',
            'operator',
            'iya',
            'ya',
            'mau',
            'boleh',
        ]);
    }

    private function declinesCustomerService(string $text): bool
    {
        $normalized = $this->normalizeText($text);
        $tokens = $this->tokenize($text);

        foreach (['tidak', 'tidak usah', 'ga usah', 'gak usah', 'nggak usah', 'tidak perlu'] as $phrase) {
            if ($normalized === $phrase || str_contains($normalized, $phrase)) {
                return true;
            }
        }

        return (bool) array_intersect($tokens, [
            'tidak',
            'ga',
            'gak',
            'nggak',
            'enggak',
            'tdk',
            'no',
        ]);
    }

    private function getOpenSupportTicket(int $chatId): ?array
    {
        return db_connect()->table('wa_support_tickets')
            ->where('chat_id', $chatId)
            ->where('status', 'open')
            ->orderBy('id', 'DESC')
            ->get()
            ->getRowArray() ?: null;
    }

    private function createSupportTicket(int $chatId, ?int $userMessageId = null): int
    {
        $db = db_connect();
        $open = $this->getOpenSupportTicket($chatId);

        if ($open) {
            return (int) $open['id'];
        }

        $now = $this->now();
        $db->table('wa_support_tickets')->insert([
            'chat_id' => $chatId,
            'user_message_id' => $userMessageId,
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

        return $ticketId;
    }

    private function webChatHasHandoffOffer(int $chatId): bool
    {
        $lastBotMessage = db_connect()->table('wa_messages')
            ->where('chat_id', $chatId)
            ->where('direction', 'outgoing')
            ->where('sender_type', 'bot')
            ->orderBy('id', 'DESC')
            ->get(1)
            ->getRowArray();

        return $lastBotMessage && $this->isCustomerServiceOffer((string) $lastBotMessage['message']);
    }

    public function chat()
    {
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

            if ($this->webChatHasHandoffOffer($chatId)) {
                if ($this->declinesCustomerService($message)) {
                    $reply = 'Baik, tidak saya hubungkan ke admin sekolah. Silakan tulis pertanyaan lain jika masih membutuhkan bantuan.';
                    $this->insertMessage($chatId, 'outgoing', 'bot', $reply, [
                        'chatbot_understood' => 1,
                        'needs_cs' => 0,
                    ]);
                    db_connect()->table('wa_messages')->where('id', $userMessageId)->update([
                        'answered_by_chatbot' => 1,
                        'chatbot_understood' => 1,
                        'needs_cs' => 0,
                        'updated_at' => $this->now(),
                    ]);

                    return $this->respond(array_merge($this->choiceResponse($reply), [
                        'chat_id' => $chatId,
                        'handoff' => false,
                    ]));
                }

                if ($this->wantsCustomerService($message)) {
                    $ticketId = $this->createSupportTicket($chatId, $userMessageId);
                    $reply = 'Baik, Anda sudah terhubung dengan admin sekolah. Mohon tunggu balasan admin.';
                    $this->insertMessage($chatId, 'outgoing', 'bot', $reply, [
                        'chatbot_understood' => 0,
                        'needs_cs' => 1,
                        'is_training_candidate' => 1,
                    ]);

                    return $this->respond(array_merge($this->choiceResponse($reply), [
                        'chat_id' => $chatId,
                        'ticket_id' => $ticketId,
                        'handoff' => true,
                    ]));
                }
            }
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

        if ($isWebChat && $chatId && $openTicket = $this->getOpenSupportTicket($chatId)) {
            $reply = 'Pesan Anda sudah diteruskan ke admin sekolah. Mohon tunggu balasan admin.';
            $this->insertMessage($chatId, 'outgoing', 'bot', $reply, [
                'chatbot_understood' => 0,
                'needs_cs' => 1,
                'is_training_candidate' => 1,
            ]);

            return $this->respond(array_merge($this->choiceResponse($reply), [
                'chat_id' => $chatId,
                'ticket_id' => (int) $openTicket['id'],
                'handoff' => true,
            ]));
        }

        $fallback = 'Maaf, saya belum bisa memahami pertanyaan Anda. Apakah Anda ingin terhubung dengan admin sekolah?';

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
}
