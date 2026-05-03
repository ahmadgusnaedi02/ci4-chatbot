<?php

namespace App\Controllers;

class Ppdb extends BaseController
{
    public function index(): string
    {
        return view('ppdb/index_ppdb');
    }
}
