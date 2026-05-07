<?php

namespace App\Controllers;

use App\Models\ChatbotKnowledgeModel;
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
        $model = new ChatbotKnowledgeModel();
        $model->seedDefaultRows();

        $keyword = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));

        $builder = $model->orderBy('priority', 'DESC')->orderBy('id', 'DESC');

        if ($status !== '') {
            $builder->where('status', $status);
        }

        if ($keyword !== '') {
            $builder->groupStart()
                ->like('pertanyaan', $keyword)
                ->orLike('intent', $keyword)
                ->orLike('keyword', $keyword)
                ->orLike('response', $keyword)
                ->groupEnd();
        }

        return view('dashboard/knowledge_base/index', [
            'items' => $builder->paginate(10, 'knowledge_base'),
            'pager' => $model->pager,
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    public function createKnowledgeBase(): string
    {
        return view('dashboard/knowledge_base/form', [
            'mode' => 'create',
            'item' => [
                'pertanyaan' => '',
                'intent' => '',
                'keyword' => '',
                'response' => '',
                'status' => 'active',
                'priority' => 0,
                'source' => 'manual',
            ],
        ]);
    }

    public function storeKnowledgeBase(): RedirectResponse
    {
        $model = new ChatbotKnowledgeModel();
        $model->ensureTable();

        $data = $this->knowledgeBasePayload();
        $validation = $this->validateKnowledgeBase($data);

        if ($validation !== true) {
            return redirect()->back()->withInput()->with('error', $validation);
        }

        $model->insert($data);

        return redirect()->to(site_url('dashboard/knowledge-base'))->with('success', 'Data knowledge base berhasil ditambahkan.');
    }

    public function editKnowledgeBase(int $id): string|RedirectResponse
    {
        $model = new ChatbotKnowledgeModel();
        $model->ensureTable();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/knowledge-base'))->with('error', 'Data knowledge base tidak ditemukan.');
        }

        return view('dashboard/knowledge_base/form', [
            'mode' => 'edit',
            'item' => $item,
        ]);
    }

    public function updateKnowledgeBase(int $id): RedirectResponse
    {
        $model = new ChatbotKnowledgeModel();
        $model->ensureTable();

        if (!$model->find($id)) {
            return redirect()->to(site_url('dashboard/knowledge-base'))->with('error', 'Data knowledge base tidak ditemukan.');
        }

        $data = $this->knowledgeBasePayload();
        $validation = $this->validateKnowledgeBase($data);

        if ($validation !== true) {
            return redirect()->back()->withInput()->with('error', $validation);
        }

        $model->update($id, $data);

        return redirect()->to(site_url('dashboard/knowledge-base'))->with('success', 'Data knowledge base berhasil diperbarui.');
    }

    public function toggleKnowledgeBase(int $id): RedirectResponse
    {
        $model = new ChatbotKnowledgeModel();
        $model->ensureTable();
        $item = $model->find($id);

        if (!$item) {
            return redirect()->to(site_url('dashboard/knowledge-base'))->with('error', 'Data knowledge base tidak ditemukan.');
        }

        $model->update($id, [
            'status' => $item['status'] === 'active' ? 'inactive' : 'active',
        ]);

        return redirect()->to(site_url('dashboard/knowledge-base'))->with('success', 'Status knowledge base berhasil diubah.');
    }

    public function deleteKnowledgeBase(int $id): RedirectResponse
    {
        $model = new ChatbotKnowledgeModel();
        $model->ensureTable();

        if (!$model->find($id)) {
            return redirect()->to(site_url('dashboard/knowledge-base'))->with('error', 'Data knowledge base tidak ditemukan.');
        }

        $model->delete($id);

        return redirect()->to(site_url('dashboard/knowledge-base'))->with('success', 'Data knowledge base berhasil dihapus.');
    }

    private function knowledgeBasePayload(): array
    {
        return [
            'pertanyaan' => trim((string) $this->request->getPost('pertanyaan')),
            'intent' => trim((string) $this->request->getPost('intent')),
            'keyword' => trim((string) $this->request->getPost('keyword')),
            'response' => trim((string) $this->request->getPost('response')),
            'status' => trim((string) $this->request->getPost('status')) ?: 'active',
            'priority' => (int) $this->request->getPost('priority'),
            'source' => trim((string) $this->request->getPost('source')) ?: 'manual',
        ];
    }

    private function validateKnowledgeBase(array $data): bool|string
    {
        if ($data['pertanyaan'] === '') {
            return 'Pertanyaan wajib diisi.';
        }

        if ($data['intent'] === '') {
            return 'Intent wajib diisi.';
        }

        if ($data['keyword'] === '') {
            return 'Keyword wajib diisi.';
        }

        if ($data['response'] === '') {
            return 'Response wajib diisi.';
        }

        if (!in_array($data['status'], ['active', 'inactive', 'draft'], true)) {
            return 'Status tidak valid.';
        }

        return true;
    }
}
