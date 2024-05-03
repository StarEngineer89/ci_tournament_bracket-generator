<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('brackets');
    }

    public function participants(): string
    {
        return view('participants-list');
    }

    public function brackets()
    {
        return view('brackets');
    }
}
