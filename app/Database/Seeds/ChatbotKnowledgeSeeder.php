<?php

namespace App\Database\Seeds;

use App\Models\ChatbotKnowledgeModel;
use CodeIgniter\Database\Seeder;

class ChatbotKnowledgeSeeder extends Seeder
{
    public function run()
    {
        (new ChatbotKnowledgeModel())->seedDefaultRows();
    }
}
