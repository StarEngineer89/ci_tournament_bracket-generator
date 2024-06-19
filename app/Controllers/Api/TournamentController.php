<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;

class TournamentController extends BaseController
{
    public function index()
    {
        //
    }

    public function save()
    {
        $tournamentModel = model('\App\Models\TournamentModel');

        $existing = $tournamentModel->where(['name' => $this->request->getPost('name'), 'user_by' => auth()->user()->id])->findAll();

        if ($existing) {
            return json_encode(['error' => "The same tournament name is existing. Please use another name."]);
        }

        $data = [
            'name' => $this->request->getPost('title'),
            'user_by' => auth()->user()->id,
            'type' => $this->request->getPost('type'),
        ];

        $tournament_id = $tournamentModel->insert($data);

        if (!$tournament_id) {
            return json_encode(['error' => "Failed to save the tournament name."]);
        }

        if ($this->request->getPost('setting-toggle')) {
            $musicSettingsModel = model('\App\Models\MusicSettingModel');

            foreach ($this->request->getPost('audioType') as $index => $value) {
                if (isset($this->request->getPost('setting-toggle')[$index]) && $this->request->getPost('setting-toggle')[$index] == 'on') {
                    $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $this->request->getPost('url')[$index];

                    $setting = [
                        'path' => $path,
                        'source' => $this->request->getPost('source')[$index],
                        'tournament_id' => $tournament_id,
                        'user_by' => auth()->user()->id,
                        'type' => $index,
                        'duration' => $this->request->getPost('duration')[$index],
                        'start' => $this->request->getPost('start')[$index],
                        'end' => $this->request->getPost('stop')[$index]
                    ];

                    $music_setting = $musicSettingsModel->insert($setting);

                    if (!$music_setting) {
                        return json_encode(['error' => "Failed to save the music settings."]);
                    }

                    $data['music'][] = $setting;
                }
            }
        }

        $data['tournament_id'] = $tournament_id;

        return json_encode(['msg' => "Success to save the tournament settings.", 'data' => $data]);
    }

    public function getMusicSettings($id)
    {
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $settings = $musicSettingModel->where(['tournament_id' => $id])->findAll();

        $html = view('tournament/music-setting', []);

        return json_encode(['msg' => "Tournament was updated successfully.", 'data' => $settings, 'html' => $html]);
    }

    public function update($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');

        $tournamentModel->update($id, $this->request->getPost());

        return json_encode(['msg' => "Tournament was updated successfully.", 'data' => $this->request->getPost()]);
    }

    public function updateMusic($tournament_id)
    {
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        foreach ($this->request->getPost('audioType') as $index => $value) {

            $settings = $musicSettingModel->where(['tournament_id' => $tournament_id, 'type' => $value])->findAll();

            if (count($settings)) {
                $setting = $settings[0];
            } else {
                $setting = [];
            }

            if (isset($this->request->getPost('setting-toggle')[$index]) && $this->request->getPost('setting-toggle')[$index] == 'on') {
                $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $this->request->getPost('url')[$index];

                $setting['path'] = $path;
                $setting['source'] = $this->request->getPost('source')[$index];
                $setting['tournament_id'] = $tournament_id;
                $setting['user_by'] = auth()->user()->id;
                $setting['type'] = $index;
                $setting['duration'] = $this->request->getPost('duration')[$index];
                $setting['start'] = $this->request->getPost('start')[$index];
                $setting['end'] = $this->request->getPost('stop')[$index];

                $musicSettingModel->save($setting);
            } else {
                if ($setting) {
                    $musicSettingModel->delete($setting['id']);
                }
            }
        }

        return json_encode(['msg' => "Tournament was updated successfully."]);
    }

    public function delete($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $bracketModel = model('\App\Models\BracketModel');
        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $musicSettingModel->where('tournament_id', $id)->delete();
        $bracketModel->where('tournament_id', $id)->delete();
        $tournamentModel->delete($id);

        return json_encode(['msg' => "Tournament was deleted successfully."]);
    }

    public function upload()
    {
        $validationRule = [
            'audio' => [
                'label' => 'Audio File',
                'rules' => [
                    'uploaded[audio]',
                    'mime_in[audio,audio/mpeg, audio/wav,audio/ogg,audio/mid,audio/x-midi]',
                ],
            ],
        ];
        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];

            return json_encode($data);
        }

        $audio = $this->request->getFile('audio');

        if (!$audio->hasMoved()) {
            $filepath = $audio->store();

            $data = ['uploaded_fileinfo' => new File($filepath), 'path' => $filepath];

            return json_encode($data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return json_encode($data);
    }

    public function fetchShareSettings($tournament_id) {
        $shareSettingsModel = model('\App\Models\ShareSettingsModel');
        $tournamentModel = model('\App\Models\TournamentModel');

        $tournament = $tournamentModel->find($tournament_id);
        if (!$tournament) {
            return json_encode(['status'=> 'failed', 'msg'=> 'Tournament was not found!']);
        }

        $settings = $shareSettingsModel->where('tournament_id', $tournament_id)->findAll();
        
        $settings_with_users = [];
        if ($settings) {
            $userModel = model('CodeIgniter\Shield\Models\UserModel');

            foreach ($settings as $setting) {
                $setting['private_users'] = null;
                
                if ($setting['target'] == SHARE_TO_USERS) {
                    $users = explode(',', $setting['users']);
                    
                    $setting['private_users'] = implode(',', array_column($userModel->select('username')->find($users), 'username'));
                }

                $settings_with_users[] = $setting;
            }
        }
        
        $config = new \Config\Encryption();
        $token = hash_hmac('sha256', 'tournament_' . $tournament_id . '_created_by_' . auth()->user()->id . '_' . time(), $config->key);

        return json_encode(['status' => 'success','settings'=> $settings_with_users, 'token' => $token]);
    }

    public function share($id)
    {
        $shareSettingsModel = model('\App\Models\ShareSettingsModel');

        $data = $this->request->getPost();
        $data['user_by'] = auth()->user()->id;

        $setting = $shareSettingsModel->where(['tournament_id' => $data['tournament_id'], 'token' => $data['token']])->first();
        if ($setting) {
            $data['id'] = $setting['id'];
        }
        
        $shareSettingsModel->save($data);

        $share = $shareSettingsModel->where(['tournament_id' => $data['tournament_id'], 'token' => $data['token']])->first();
        
        $share['private_users'] = null;
        if ($share['target'] == SHARE_TO_USERS) {
            $userModel = model('CodeIgniter\Shield\Models\UserModel');
            $users = explode(',', $share['users']);
            
            $share['private_users'] = implode(',', array_column($userModel->select('username')->find($users), 'username'));
        }
        
        return json_encode(['msg' => "Success to save the sharing information.", 'share' => $share]);
    }

    public function purgechShareSettings($share_id) {
        $shareSettingsModel = model('\App\Models\ShareSettingsModel');
        $share = $shareSettingsModel->find($share_id);

        $shareSettingsModel->delete([$share_id]);
        $shares = $shareSettingsModel->where(['tournament_id'=> $share['tournament_id']])->findAll();

        return json_encode(['status'=> 'success', 'shares'=> $shares, 'tournament_id' => $share['tournament_id']]);
    }

    public function fetchShareSetting($share_id) {
        $shareSettingsModel = model('\App\Models\ShareSettingsModel');

        $share = $shareSettingsModel->find($share_id);
        $share['private_users'] = null;
        if ($share['target'] == SHARE_TO_USERS) {
            $userModel = model('CodeIgniter\Shield\Models\UserModel');
            $users = explode(',', $share['users']);
            
            $share['private_users'] = $userModel->find($users);
        }

        return json_encode(['status'=> 'success', 'share' => $share]);
    }

    public function getActionHistory($tournament_id)
    {
        $logActionsModel = model('\App\Models\LogActionsModel');

        $history = $logActionsModel->getLogs()->where('tournament_id', $tournament_id)->findAll();

        $data = [];
        if ($history && count($history)) {
            foreach ($history as $row) {
                $params = json_decode($row['params']);
                $participants = $params->participants;
                if ($row['action'] == BRACKET_ACTIONCODE_MARK_WINNER) {
                    $action = "Participant \"$participants[0]\" in bracket #$params->bracket_no marked as a winner in round $params->round_no";
                }

                if ($row['action'] == BRACKET_ACTIONCODE_UNMARK_WINNER) {
                    $action = "Participant \"$participants[0]\" in bracket #$params->bracket_no unmarked winner in round $params->round_no";
                }

                if ($row['action'] == BRACKET_ACTIONCODE_CHANGE_PARTICIPANT) {
                    $action = "Participant \"$participants[0]\" in bracket #$params->bracket_no changed to the following Participant: \"$participants[1]\" in round $params->round_no";
                }

                if ($row['action'] == BRACKET_ACTIONCODE_ADD_PARTICIPANT) {
                    $action = "Participant \"$participants[0]\" added in bracket #$params->bracket_no in round $params->round_no";
                }

                if ($row['action'] == BRACKET_ACTIONCODE_DELETE) {
                    $action = "Bracket #$params->bracket_no containing participants [\"$participants[0]\", \"$participants[1]\"] in round $params->round_no deleted";
                }

                $data[] = [
                    'name' => $row['username'],
                    'action' => $action,
                    'time' => $row['updated_at']
                ];
            }
        }

        return json_encode(['result' => 'success', 'history' => $data, 'tournament_id' => $tournament_id]);
    }

    public function fetchUsersList()
    {
        $userModel = model('CodeIgniter\Shield\Models\UserModel');

        if ($this->request->getPost('query')) {
            $userModel->like('username', $this->request->getPost('query'));
        }        
        
        $users = $userModel->select(['id', 'username'])->findAll();

        return json_encode(['result'=> 'success','users'=> $users, 'query' => $this->request->getPost()]);
    }
}