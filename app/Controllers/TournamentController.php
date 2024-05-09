<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentController extends BaseController
{
    public function index()
    {
        $TournamentModel = model('\App\Models\TournamentModel');

        $brackets = $TournamentModel->where('user_by', auth()->user()->id)->findAll();

        return view('tournament/dashboard');
    }

    public function create()
    {
        $ParticipantModel = model('\App\Models\ParticipantModel');

        $participants = $ParticipantModel->where('user_by', auth()->user()->id)->findAll();

        return view('tournament/create', ['participants' => $participants]);
    }

}
