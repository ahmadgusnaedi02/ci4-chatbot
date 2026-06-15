<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session('admin_logged_in')) {
            return redirect()->to(site_url('admin/login'))->with('error', 'Silakan login sebagai admin sekolah.');
        }

        $permissions = service('permissions');
        $role = $permissions->normalizeRole((string) session('admin_role'));

        if ($role !== session('admin_role')) {
            session()->set('admin_role', $role);
        }

        $path = trim($request->getUri()->getPath(), '/');

        if (!$permissions->canAccessPath($path, $request->getMethod())) {
            if ($path === 'dashboard') {
                return service('response')->setStatusCode(403)->setBody('Anda tidak memiliki hak akses untuk membuka dashboard.');
            }

            return redirect()->to(site_url('dashboard'))->with('error', 'Anda tidak memiliki hak akses untuk membuka menu tersebut.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
