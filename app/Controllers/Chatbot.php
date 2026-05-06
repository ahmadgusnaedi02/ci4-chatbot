<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Chatbot extends ResourceController
{
    private array $knowledgeBase = [
        [
            'question' => 'kapan pendaftaran ppdb',
            'answer' => 'Pendaftaran PPDB dibuka pada bulan Juni setiap tahunnya.',
        ],
        [
            'question' => 'syarat ppdb',
            'answer' => 'Syarat PPDB adalah fotokopi ijazah, kartu keluarga, dan pas foto.',
        ],
        [
            'question' => 'alamat sekolah',
            'answer' => 'Alamat sekolah berada di Jl. Contoh No. 10 Kediri.',
        ],
    ];

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

        $best = [
            'distance' => PHP_INT_MAX,
            'answer' => null,
        ];

        foreach ($this->knowledgeBase as $item) {
            $target = $this->normalizeText($item['question']);
            $distance = levenshtein($query, $target);

            if ($distance < $best['distance']) {
                $best = [
                    'distance' => $distance,
                    'answer' => $item['answer'],
                ];
            }
        }

        $maxDistance = max(1, (int) floor(strlen($query) * 0.4));
        if ($best['answer'] && $best['distance'] <= $maxDistance) {
            return $best['answer'];
        }

        $queryTokens = array_values(array_filter($this->tokenize($message), fn ($token) => strlen($token) > 2));
        if (!$queryTokens) {
            return null;
        }

        $bestScore = 0;
        $bestAnswer = null;

        foreach ($this->knowledgeBase as $item) {
            $itemTokens = array_values(array_filter($this->tokenize($item['question']), fn ($token) => strlen($token) > 2));
            $matches = count(array_intersect($itemTokens, $queryTokens));
            $score = ($matches / count($queryTokens)) + ($matches / max(1, count($itemTokens)));

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestAnswer = $item['answer'];
            }
        }

        return $bestScore >= 1 ? $bestAnswer : null;
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

    public function chat()
    {
        $apiKey = getenv('GROQ_API_KEY');

        $input = $this->request->getJSON();
        $message = trim((string) ($input->message ?? ''));

        if ($message === '') {
            return $this->respond($this->choiceResponse('Maaf, pesan Anda kosong.'));
        }

        $localAnswer = $this->findLocalAnswer($message);
        if ($localAnswer) {
            return $this->respond($this->choiceResponse($localAnswer));
        }

        if (!$apiKey) {
            return $this->respond($this->choiceResponse('Maaf, saya belum bisa memahami pertanyaan Anda. Apakah Anda ingin terhubung dengan CS?'));
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

        return $this->respond(json_decode($response->getBody()));
    }
}
