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
        $tournaments = $tournamentsModel->where(['visibility' => 1]);
        $searchString = '';
        if ($this->request->getGet('query')) {
            $searchString = $this->request->getGet('query');
            $tournaments->like(['tournaments.searchable' => $searchString]);
        }
        
        $tournaments = $tournaments->findAll();

        return view('gallery', ['tournaments' => $tournaments, 'searchString' => $searchString]);
    }
}