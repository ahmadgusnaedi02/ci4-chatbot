<?php

namespace App\Controllers;

use App\Models\LandingPageModel;

class Home extends BaseController
{
    public function index(): string
    {
        $model = new LandingPageModel();

        return view('home/index_home', [
            'landingSettings' => $model->getSettings(),
            'landingPrograms' => $model->getPrograms(true),
            'landingStaff' => $model->getStaff(true),
            'landingNews' => $model->getNews(true),
        ]);

    }
}
