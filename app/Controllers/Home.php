<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return view('home');
    }

    public function gallery()
    {
        $tournamentsModel = model('\App\Models\TournamentModel');
        $tournaments = $tournamentsModel->where(['visibility' => 1])->findAll();

        return view('gallery', ['tournaments' => $tournaments]);
    }
}