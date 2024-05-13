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

        $musicSettingsBlock = view('tournament/music-setting', []);

        return view('tournament/dashboard', ['tournaments' => $tournaments, 'musicSettingsBlock' => $musicSettingsBlock]);
    }

    public function create()
    {
        $participantModel = model('\App\Models\ParticipantModel');

        $participants = $participantModel->where('user_by', auth()->user()->id)->findAll();

        $musicSettingsBlock = view('tournament/music-setting', []);

        return view('tournament/create', ['participants' => $participants, 'musicSettingsBlock' => $musicSettingsBlock]);
    }

    public function view($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');        
        $bracketModel = model('\App\Models\BracketModel');        
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $tournament = $tournamentModel->find($id);
        $brackets = $bracketModel->where('tournament_id', $id)->findAll();
        $settings = $musicSettingModel->where(['tournament_id' => $id, 'type' => 0])->findAll();

        if ($brackets) {
            return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'settings' => $settings]);
        }

        $participantModel = model('\App\Models\ParticipantModel');

        $participants = $participantModel->where('user_by', auth()->user()->id)->findAll();

        $musicSettingsBlock = view('tournament/music-setting', []);

        return view('tournament/create', ['participants' => $participants, 'tournament' => $tournament, 'settings' => $settings, 'musicSettingsBlock' => $musicSettingsBlock]);
    }
}
