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
        $BracketModel = model('\App\Models\BracketModel');

        $brackets = $BracketModel->where('user_by', auth()->user()->id)->findAll();

        return view('brackets', ['brackets' => $brackets]);
    }
}
