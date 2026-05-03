<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class Chatbot extends ResourceController
{
    public function chat()
    {
        $apiKey = getenv('GROQ_API_KEY');

        $input = $this->request->getJSON();
        $message = $input->message ?? '';

        $client = \Config\Services::curlrequest();

        $response = $client->post('https://api.groq.com/openai/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'llama-3.1-8b-instant',
                'messages' => [
                    ['role' => 'user', 'content' => $message]
                ],
            ]
        ]);

        return $this->respond(json_decode($response->getBody()));
    }
}