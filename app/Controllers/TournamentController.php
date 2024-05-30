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

        if (!$tournament) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "This tournament is not existing!"]);

            return redirect()->to('/tournaments');
        }

        /**
         * Check if the tournament was shared to the user
         */
        $shareSettingModel = model('\App\Models\ShareSettingsModel');
        $shareSetting = $shareSettingModel->where('tournament_id', $id)->first();

        if (is_null(auth()->user())) {
            if (!$shareSetting || $shareSetting['target'] != SHARE_TO_EVERYONE) {
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "You should be logged in to view this tournament!"]);

                return redirect()->to('/login');
            }
        }

        if (!$shareSetting && $tournament['user_by'] != auth()->user()->id) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "You don't have permission to view this tournament!"]);

            return redirect()->to('/tournaments');
        }

        $brackets = $bracketModel->where('tournament_id', $id)->findAll();
        $settings = $musicSettingModel->where(['tournament_id' => $id, 'type' => 0])->findAll();

        if (!$brackets) {
            if ($tournament['user_by'] != auth()->user()->id) {
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "The brackets was not generated yet."]);

                return redirect()->to('/tournaments');
            }

            $participantModel = model('\App\Models\ParticipantModel');

            $participants = $participantModel->where('user_by', auth()->user()->id)->findAll();

            $musicSettingsBlock = view('tournament/music-setting', []);

            return view('tournament/create', ['participants' => $participants, 'tournament' => $tournament, 'settings' => $settings, 'musicSettingsBlock' => $musicSettingsBlock]);
        }

        return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'settings' => $settings]);
    }
}
