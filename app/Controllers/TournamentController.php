<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentController extends BaseController
{
    public function index()
    {
        $tournamentModel = model('\App\Models\TournamentModel');

        $tournaments = $tournamentModel->where('user_by', auth()->user()->id)->findAll();

        return view('tournament/dashboard', ['tournaments' => $tournaments]);
    }

    public function create()
    {
        $participantModel = model('\App\Models\ParticipantModel');

        $participants = $participantModel->where('user_by', auth()->user()->id)->findAll();

        return view('tournament/create', ['participants' => $participants]);
    }

    public function view($id)
    {
        $bracketModel = model('\App\Models\BracketModel');

        $participants = $bracketModel->where('tournament_id', $id)->findAll();

        return view('tournament/create', ['participants' => $participants]);
    }
}
