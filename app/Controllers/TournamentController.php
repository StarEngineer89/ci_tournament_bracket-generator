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

        if ($this->request->getGet('filter') == 'archived') {
            $tournaments = $tournamentModel->where(['user_by' => auth()->user()->id, 'status' => TOURNAMENT_STATUS_COMPLETED])->findAll();
        }

        $navActive = ($this->request->getGet('filter')) ? $this->request->getGet('filter') :'all';

        if ($this->request->getGet('filter') == 'shared') {
            $shareSettingsModel = model('\App\Models\ShareSettingsModel');
            
            if ($this->request->getGet('type') == 'wh') {
                // $tempRows = $shareSettingsModel->tournamentDetails()->Like('users', strval(auth()->user()->id))->findAll();
                $tempRows = $shareSettingsModel->tournamentDetails()->where('target', SHARE_TO_EVERYONE)->orLike('users', strval(auth()->user()->id))->findAll();
                
                $tournaments = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $user_ids = explode(',', $tempRow['users']);

                        if ($tempRow['target'] == SHARE_TO_USERS && in_array(auth()->user()->id, $user_ids)) {
                            $tournaments[$tempRow['tournament_id']] = $tempRow;
                        }

                        if ($tempRow['target'] == SHARE_TO_EVERYONE && !isset($tournaments[$tempRow['tournament_id']])) {
                            if ($tempRow['access_time']) {
                                $tournaments[$tempRow['tournament_id']] = $tempRow;
                            }
                        }
                    }
                }

                $table = view('tournament/shared-with-me', ['tournaments' => $tournaments, 'shareType' => $this->request->getGet('type')]);
            } else {
                $tempRows = $shareSettingsModel->tournamentDetails()->where('share_settings.user_by', auth()->user()->id)->findAll();

                $tournaments = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $tournaments[$tempRow['tournament_id']] = $tempRow;
                    }
                }

                $table = view('tournament/shared-by-me', ['tournaments' => $tournaments, 'shareType' => $this->request->getGet('type'), 'navActive' => $navActive]);
            }

            
        } else {
            $table = view('tournament/list', ['tournaments' => $tournaments, 'navActive' => $navActive]);
        }

        $musicSettingsBlock = view('tournament/music-setting', []);

        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $users = $userModel->select(['id', 'username'])->findAll();

        return view('tournament/dashboard', ['table' => $table, 'musicSettingsBlock' => $musicSettingsBlock, 'users' => $users, 'navActive' => $navActive]);
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
    
    public function viewShared($token)
    {
        $shareSettingModel = model('\App\Models\ShareSettingsModel');
        $tournamentModel = model('\App\Models\TournamentModel');
        $bracketModel = model('\App\Models\BracketModel');
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $settings = $shareSettingModel->where(['token'=> $token])->first();
        if (!$settings) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "This link is incorrect!"]);

            return redirect()->to('/tournaments');
        }

        $tournament = $tournamentModel->find($settings['tournament_id']);
        
        if (!$tournament) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "This tournament is not existing!"]);

            return redirect()->to('/tournaments');
        }

        $brackets = $bracketModel->where('tournament_id', $settings['tournament_id'])->findAll();
        $musicSettings = $musicSettingModel->where(['tournament_id' => $settings['id'], 'type' => 0])->findAll();

        $shareAccessModel = model('\App\Models\TournamentShareAccessLogModel');
        if (auth()->user()) {
            $shareAccessModel->insert(['share_id' => $settings['id'], 'user_by' => auth()->user()->id]);
        } else {
            $shareAccessModel->insert(['share_id' => $settings['id'], 'user_by' => 0]);
        }
        

        if (!$brackets) {
            if (empty(auth()->user()) || $tournament['user_by'] != auth()->user()->id) {
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "The brackets was not generated yet."]);

                return redirect()->to('/tournaments');
            }

            $participantModel = model('\App\Models\ParticipantModel');

            $participants = $participantModel->where('user_by', auth()->user()->id)->findAll();

            $musicSettingsBlock = view('tournament/music-setting', []);

            return view('tournament/create', ['participants' => $participants, 'tournament' => $tournament, 'settings' => $musicSettings, 'musicSettingsBlock' => $musicSettingsBlock, 'permission' => $settings['permission']]);
        }

        return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'settings' => $settings]);
    }
}