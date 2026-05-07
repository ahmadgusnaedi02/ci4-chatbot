<?php

namespace App\Database\Seeds;

use App\Models\ChatbotIntentModel;
use CodeIgniter\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    public function run()
    {
        (new ChatbotIntentModel())->ensureSchema();
    }
}
