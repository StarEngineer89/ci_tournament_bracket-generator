<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentController extends BaseController
{
    public function index()
    {
        $navActive = ($this->request->getGet('filter')) ? $this->request->getGet('filter') :'all';
        $searchString = null;

        if ($this->request->getGet('filter') == 'shared') {
            $shareSettingsModel = model('\App\Models\ShareSettingsModel');
            $tournaments = $shareSettingsModel->tournamentDetails();
            
            if ($this->request->getGet('query')) {
                $searchString = $this->request->getGet('query');
                $tournaments->like(['tournaments.searchable' => $searchString]);
            }
            
            if ($this->request->getGet('type') == 'wh') {
                $tempRows = $tournaments->where('target', SHARE_TO_EVERYONE)->orWhere('target', SHARE_TO_PUBLIC)->orLike('users', strval(auth()->user()->id))->findAll();
                
                $tournaments = [];
                $access_tokens = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $user_ids = explode(',', $tempRow['users']);
                        
                        $add_in_list = false;
                        if ($tempRow['target'] == SHARE_TO_USERS && in_array(auth()->user()->id, $user_ids)) {
                            $add_in_list = true;
                        }

                        if (($tempRow['target'] == SHARE_TO_EVERYONE || $tempRow['target'] == SHARE_TO_PUBLIC) && $tempRow['access_time']) {
                            $add_in_list = true;
                        }

                        /** Omit the record from Shared with me if the share was created by himself */
                        if ($tempRow['user_id'] == auth()->user()->id || $tempRow['deleted_at']) {
                            $add_in_list = false;
                        }

                        if ($add_in_list && !in_array($tempRow['token'], $access_tokens)) {
                            $tournaments[] = $tempRow;
                            $access_tokens[] = $tempRow['token'];
                        }
                    }
                }

                $table = view('tournament/shared-with-me', ['tournaments' => $tournaments, 'shareType' => $this->request->getGet('type'), 'searchString' => $searchString]);
            } else {
                $tempRows = $tournaments->where('share_settings.user_id', auth()->user()->id)->findAll();

                $tournaments = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $tournaments[$tempRow['tournament_id']] = $tempRow;
                    }
                }

                $table = view('tournament/shared-by-me', ['tournaments' => $tournaments, 'shareType' => $this->request->getGet('type'), 'navActive' => $navActive, 'searchString' => $searchString]);
            }

            
        } else {
            $tournamentModel = model('\App\Models\TournamentModel');
            $userModel = model('CodeIgniter\Shield\Models\UserModel');
            $userIdentityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');

            $tournaments = $tournamentModel->where(['user_id' => auth()->user()->id]);

            if ($this->request->getGet('filter') == 'archived') {
                $tournaments->where(['archive' => 1]);
            } else {
                $tournaments->where('archive', 0);
            }

            if ($this->request->getGet('query')) {
                $searchString = $this->request->getGet('query');
                $tournaments->like(['searchable' => $searchString]);
            }
            // var_dump($tournaments->builder()->getCompiledSelect());
            $tournaments = $tournaments->findAll();

            $tns = [];

            foreach($tournaments as $tournament){
                $temp = $tournament;
                
                $temp['username'] = 'Guest';
                $temp['email'] = '';
                if($tournament['user_id'] > 0){
                    $user = $userModel->find($tournament['user_id']);
                    $userId = $userIdentityModel->find($tournament['user_id']);
                    $temp['username'] = $user->username;
                    $temp['email'] = $userId->secret;
                }
                
                $participantModel = model('\App\Models\ParticipantModel');
                $temp['participants'] = count($participantModel->where('tournament_id', $tournament['id'])->findAll());

                $shareSettingModel = model('\App\Models\ShareSettingsModel');
                $sharedTournament = $shareSettingModel->where(['tournament_id' => $tournament['id'], 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
                $temp['public_url'] = '';
                if($sharedTournament) $temp['public_url'] = base_url('/tournaments/shared/') . $sharedTournament['token'];
                $tns[] = $temp;
            }
            
            $table = view('tournament/list', ['tournaments' => $tns, 'navActive' => $navActive, 'searchString' => $searchString]);
        }

        $settingsBlock = view('tournament/tournament-settings', []);
        $musicSettingsBlock = view('tournament/music-setting', []);

        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $users = $userModel->select(['id', 'username'])->findAll();

        return view('tournament/dashboard', ['table' => $table, 'musicSettingsBlock' => $musicSettingsBlock, 'settingsBlock' => $settingsBlock, 'users' => $users, 'navActive' => $navActive]);
    }

    public function create()
    {
        $userSettingModel = model('\App\Models\UserSettingModel');
        $participantModel = model('\App\Models\ParticipantModel');

        // Convert settings to key-value array
        $settingsArray = [];
        if( auth()->user() ){

            $participants = $participantModel->where(['user_id' => auth()->user()->id, 'tournament_id' => 0])->delete();

            $userSettings = $userSettingModel->where('user_id', auth()->user()->id)->findAll();
            if (count($userSettings)) {
                foreach ($userSettings as $setting) {
                    $settingsArray[$setting['setting_name']] = $setting['setting_value'];
                }
            }
        }

        $settingsBlock = view('tournament/tournament-settings', []);
        $musicSettingsBlock = view('tournament/music-setting', []);

        return view('tournament/create', ['musicSettingsBlock' => $musicSettingsBlock, 'settingsBlock' => $settingsBlock, 'userSettings' => $settingsArray]);
    }

    public function view($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $bracketModel = model('\App\Models\BracketModel');
        $musicSettingModel = model('\App\Models\MusicSettingModel');
        $userSettingModel = model('\App\Models\UserSettingModel');

        $shareSettingsModel = model('\App\Models\ShareSettingsModel');

        $tournament = $tournamentModel->find($id);

        if (!$tournament) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "This tournament is not existing!"]);

            return redirect()->to('/tournaments');
        }

        /** Check if the tournament is associated with guest user */
        $editable = false;
        if($tournament['user_id'] == 0){
            $existingHistory = $this->request->getCookie('guest_tournaments');
            $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];
            $shareSetting = $shareSettingsModel->where(['tournament_id' => $id, 'user_id' => 0])->first();

            $cookie_value = $id . "_" . $shareSetting['token'];

            if (in_array($cookie_value, $tournamentHistory)) {
                $editable = true;
            }
        }
        /** End check */
        
        $brackets = $bracketModel->where('tournament_id', $id)->findAll();
        
        if (!$brackets) {
            if ($tournament['user_id'] != auth()->user()->id) {
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "The brackets was not generated yet."]);

                return redirect()->to('/tournaments');
            }

            $participantModel = model('\App\Models\ParticipantModel');

            $participants = $participantModel->where(['user_id' => auth()->user()->id, 'tournament_id' => $id])->findAll();

            $settingsBlock = view('tournament/tournament-settings', []);
            $musicSettingsBlock = view('tournament/music-setting', []);
            $musicSettings = $musicSettingModel->where(['tournament_id' => $id, 'type' => MUSIC_TYPE_BRACKET_GENERATION])->orderBy('type','asc')->findAll();
            
            $userSettings = $userSettingModel->where('user_id', auth()->user()->id)->findAll();

            // Convert settings to key-value array
            $settingsArray = [];
            if (count($userSettings)) {
                foreach ($userSettings as $setting) {
                    $settingsArray[$setting['setting_name']] = $setting['setting_value'];
                }
            }

            return view('tournament/create', ['participants' => json_encode($participants), 'tournament' => $tournament, 'settings' => $musicSettings, 'settingsBlock' => $settingsBlock, 'musicSettingsBlock' => $musicSettingsBlock, 'userSettings' => $settingsArray]);
        }

        $musicSettings = $musicSettingModel->where(['tournament_id' => $id, 'type' => MUSIC_TYPE_FINAL_WINNER])->orderBy('type','asc')->findAll();

        return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'musicSettings' => $musicSettings, 'editable' => $editable]);
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

            return redirect()->to('/gallery');
        }else{
            if(time() - strtotime($settings['created_at']) > 24*60*60){
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "This link has been expired!"]);
                log_message('debug', "This page has been expired.");
                $shareSettingModel->where(['token'=> $token])->delete();

                return redirect()->to('/gallery');
            }
        }

        $tournament = $tournamentModel->find($settings['tournament_id']);
        
        if (!$tournament) {
            $session = \Config\Services::session();
            $session->setFlashdata(['error' => "This tournament is not existing!"]);

            return redirect()->to('/gallery');
        }

        $brackets = $bracketModel->where('tournament_id', $settings['tournament_id'])->findAll();

        $shareAccessModel = model('\App\Models\TournamentShareAccessLogModel');
        if (auth()->user()) {
            $shareAccessModel->insert(['share_id' => $settings['id'], 'user_id' => auth()->user()->id]);
        } else {
            //$shareAccessModel->insert(['share_id' => $settings['id'], 'user_id' => 0]);
        }
        

        if (!$brackets) {
            if (empty(auth()->user()) || $tournament['user_id'] != auth()->user()->id) {
                $session = \Config\Services::session();
                $session->setFlashdata(['error' => "The brackets was not generated yet."]);

                return redirect()->to('/tournaments');
            }

            $participantModel = model('\App\Models\ParticipantModel');

            $participants = $participantModel->where('user_id', auth()->user()->id)->findAll();

            $musicSettings = $musicSettingModel->where(['tournament_id' => $settings['id'], 'type' => MUSIC_TYPE_BRACKET_GENERATION])->findAll();
            $musicSettingsBlock = view('tournament/music-setting', []);

            return view('tournament/create', ['participants' => $participants, 'tournament' => $tournament, 'settings' => $settings, 'musicSettings' => $musicSettings, 'musicSettingsBlock' => $musicSettingsBlock, 'permission' => $settings['permission']]);
        }

        $musicSettings = $musicSettingModel->where(['tournament_id' => $settings['tournament_id'], 'type' => MUSIC_TYPE_FINAL_WINNER])->orderBy('type','asc')->findAll();
        return view('brackets', ['brackets' => $brackets, 'tournament' => $tournament, 'settings' => $settings, 'musicSettings' => $musicSettings]);
    }

    public function export()
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        
        if ($this->request->getGet('filter') == 'shared') {
            $shareSettingsModel = model('\App\Models\ShareSettingsModel');
            $tournaments = $shareSettingsModel->tournamentDetails();
            
            if ($this->request->getGet('query')) {
                $searchString = $this->request->getGet('query');
                $tournaments->like(['tournaments.searchable' => $searchString]);
            }
            
            if ($this->request->getGet('type') == 'wh') {
                $tempRows = $tournaments->where('target', SHARE_TO_EVERYONE)->orWhere('target', SHARE_TO_PUBLIC)->orLike('users', strval(auth()->user()->id))->findAll();
                
                $tournaments = [];
                $access_tokens = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $user_ids = explode(',', $tempRow['users']);
                        
                        $add_in_list = false;
                        if ($tempRow['target'] == SHARE_TO_USERS && in_array(auth()->user()->id, $user_ids)) {
                            $add_in_list = true;
                        }

                        if (($tempRow['target'] == SHARE_TO_EVERYONE || $tempRow['target'] == SHARE_TO_PUBLIC) && $tempRow['access_time']) {
                            $add_in_list = true;
                        }

                        /** Omit the record from Shared with me if the share was created by himself */
                        if ($tempRow['user_id'] == auth()->user()->id || $tempRow['deleted_at']) {
                            $add_in_list = false;
                        }

                        if ($add_in_list && !in_array($tempRow['token'], $access_tokens)) {
                            $tournaments[] = $tempRow;
                            $access_tokens[] = $tempRow['token'];
                        }
                    }
                }
            } else {
                $tempRows = $tournaments->where('share_settings.user_id', auth()->user()->id)->findAll();

                $tournaments = [];
                if ($tempRows) {
                    foreach ($tempRows as $tempRow) {
                        $tournaments[$tempRow['tournament_id']] = $tempRow;
                    }
                }
            }
        } else {
            $tournaments = $tournamentModel->where(['user_id' => auth()->user()->id]);

            if ($this->request->getGet('filter') == 'archived') {
                $tournaments->where(['archive' => 1]);
            } else {
                $tournaments->where('archive', 0);
            }

            if ($this->request->getGet('query')) {
                $searchString = $this->request->getGet('query');
                $tournaments->like(['searchable' => $searchString]);
            }
            
            $tournaments = $tournaments->findAll();
        }

        $filename = 'tournaments_' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        if ($this->request->getGet('filter') == 'shared' && $this->request->getGet('type') == 'wh') {
            fputcsv($output, ['ID', 'Name', 'Type', 'Status', 'Accessbility', 'Shared By', 'Shared Time', 'URL']);
        } else {
            fputcsv($output, ['ID', 'Name', 'Type', 'Status', 'Created Time', 'URL']);
        }

        // Fetch the data and write it to the CSV
        foreach ($tournaments as $tournament) {
            $statusLabel = TOURNAMENT_STATUS_LABELS[$tournament['status']];
            $type = $tournament['type'] == 1 ? 'Single' : 'Double';

            $tournamentId = ($tournament['tournament_id']) ?? $tournament['id'];

            if ($this->request->getGet('filter') == 'shared' && $this->request->getGet('type') == 'wh') {
                if ($tournament['permission'] == SHARE_PERMISSION_EDIT) {
                    $tournament['permission'] = 'Can Edit';
                }

                if ($tournament['permission'] == SHARE_PERMISSION_VIEW) {
                    $tournament['permission'] = 'Can View';
                }

                $url = base_url('tournaments/shared/' . $tournament['token']);
                $createdTime = convert_to_user_timezone($tournament['access_time'], user_timezone(auth()->user()->id));
                fputcsv($output, [
                    $tournamentId,
                    $tournament['name'],
                    $type,
                    $statusLabel,
                    $tournament['permission'],
                    $tournament['username'],
                    $createdTime,
                    $url
                ]);
            } else {
                $createdTime = convert_to_user_timezone($tournament['created_at'], user_timezone(auth()->user()->id));
                fputcsv($output, [
                    $tournamentId,
                    $tournament['name'],
                    $type,
                    $statusLabel,
                    $createdTime,
                    base_url('tournaments/' . $tournamentId . '/view')
                ]);
            }
        }

        fclose($output);
        exit;
    }

    public function apply()
    {
        return view('tournament/apply');
    }

    public function saveApply()
    {
        // Validate the input
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'name' => 'required',
            'participation_mode' => 'required',
            'agree' => 'required'
        ]);

        if (!$this->validate($validation->getRules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Process the registration (e.g., save to database)

        return redirect()->to('/tournaments/apply')->with('message', 'Registration successful');
    }
}