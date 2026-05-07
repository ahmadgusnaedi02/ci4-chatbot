<?php

namespace App\Controllers;

use App\Models\ChatbotIntentModel;
use CodeIgniter\HTTP\RedirectResponse;

class Dashboard extends BaseController
{
    public function index(): string
    {
        return view('dashboard/index_dashboard');

    }

    public function scanWhatsapp(): string
    {
        return view('dashboard/scan_whatsapp');
    }

    public function supportChat(): string
    {
        return view('dashboard/support_chat');
    }

    public function historyChat(): string
    {
        return view('dashboard/history_chat');
    }

    public function knowledgeBase(): string
    {
        return $this->intents();
    }

    public function intents(): string
    {
        $model = new ChatbotIntentModel();
        $model->ensureSchema();

        $keyword = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        return view('dashboard/intents/index', [
            'items' => $model->getIntentRows($keyword, $status),
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    public function createKnowledgeBase(): string
    {
        return $this->createIntent();
    }

    public function createIntent(): string
    {
        return view('dashboard/intents/form', [
            'mode' => 'create',
            'item' => [
                'name' => '',
                'response' => '',
                'status' => 'active',
                'priority' => 0,
                'source' => 'manual',
            ],
        ]);
    }

    public function storeKnowledgeBase(): RedirectResponse
    {
        return $this->storeIntent();
    }

    public function storeIntent(): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createIntent($this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Intent berhasil ditambahkan.');
    }

    public function editKnowledgeBase(int $id): string|RedirectResponse
    {
        return $this->editIntent($id);
    }

    public function editIntent(int $id): string|RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $model->ensureSchema();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/intents'))->with('error', 'Intent tidak ditemukan.');
        }

        return view('dashboard/intents/form', [
            'mode' => 'edit',
            'item' => $item,
        ]);
    }

    public function updateKnowledgeBase(int $id): RedirectResponse
    {
        return $this->updateIntent($id);
    }

    public function updateIntent(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateIntentRow($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Intent berhasil diperbarui.');
    }

    public function toggleKnowledgeBase(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();
        $model->ensureSchema();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/knowledge-base'))->with('error', 'Data knowledge base tidak ditemukan.');
        }

        $model->update($id, [
            'status' => $item['status'] === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Status intent berhasil diubah.');
    }

    public function deleteKnowledgeBase(int $id): RedirectResponse
    {
        return $this->deleteIntent($id);
    }

    public function deleteIntent(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->deleteIntentRow($id);
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/intents'))->with('success', 'Intent berhasil dihapus.');
    }

    public function trainingPhrases(): string
    {
        $model = new ChatbotIntentModel();
        $keyword = trim((string) $this->request->getGet('q'));
        $intentId = (int) $this->request->getGet('intent_id');

        return view('dashboard/training_phrases/index', [
            'items' => $model->getTrainingPhraseRows($keyword, $intentId),
            'intents' => $model->getSimpleIntents(),
            'keyword' => $keyword,
            'intentId' => $intentId,
        ]);
    }

    public function storeTrainingPhrase(): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createTrainingPhrase($this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', 'Training phrase berhasil ditambahkan.');
    }

    public function updateTrainingPhrase(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateTrainingPhrase($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', 'Training phrase berhasil diperbarui.');
    }

    public function deleteTrainingPhrase(int $id): RedirectResponse
    {
        (new ChatbotIntentModel())->deleteTrainingPhrase($id);

        return redirect()->to(site_url('dashboard/training-phrases'))->with('success', 'Training phrase berhasil dihapus.');
    }

    public function keywords(): string
    {
        $model = new ChatbotIntentModel();
        $keyword = trim((string) $this->request->getGet('q'));
        $intentId = (int) $this->request->getGet('intent_id');

        return view('dashboard/keywords/index', [
            'items' => $model->getKeywordRows($keyword, $intentId),
            'intents' => $model->getSimpleIntents(),
            'keyword' => $keyword,
            'intentId' => $intentId,
        ]);
    }

    public function storeKeyword(): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createKeyword($this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/keywords'))->with('success', 'Keyword berhasil ditambahkan.');
    }

    public function updateKeyword(int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateKeyword($id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/keywords'))->with('success', 'Keyword berhasil diperbarui.');
    }

    public function deleteKeyword(int $id): RedirectResponse
    {
        (new ChatbotIntentModel())->deleteKeyword($id);

        return redirect()->to(site_url('dashboard/keywords'))->with('success', 'Keyword berhasil dihapus.');
    }

    public function nlpRules(): string
    {
        $model = new ChatbotIntentModel();

        return view('dashboard/nlp_rules/index', $model->getNlpRuleDataset());
    }

    public function storeNlpRule(string $type): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->createNlpRule($type, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/nlp-rules') . '#' . $type)->with('success', 'Rule NLP berhasil ditambahkan.');
    }

    public function updateNlpRule(string $type, int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->updateNlpRule($type, $id, $this->request->getPost());
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->withInput()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/nlp-rules') . '#' . $type)->with('success', 'Rule NLP berhasil diperbarui.');
    }

    public function deleteNlpRule(string $type, int $id): RedirectResponse
    {
        $model = new ChatbotIntentModel();

        try {
            $model->deleteNlpRule($type, $id);
        } catch (\InvalidArgumentException $error) {
            return redirect()->back()->with('error', $error->getMessage());
        }

        return redirect()->to(site_url('dashboard/nlp-rules') . '#' . $type)->with('success', 'Rule NLP berhasil dihapus.');
    }

}
