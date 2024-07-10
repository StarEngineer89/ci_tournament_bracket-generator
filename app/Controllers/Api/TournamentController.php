<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
use YoutubeDl\YoutubeDl;
use YoutubeDl\Options;
use App\Services\NotificationService;

class TournamentController extends BaseController
{
    protected $notificationService;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
    }
    
    public function index()
    {
        //
    }

    public function fetch()
    {
        $model = model('\App\Models\TournamentModel');

        // Get the user_id parameter from the request
        $userBy = $this->request->getPost('user_id');

        // Apply the filter if the user_id parameter is provided
        if ($userBy) {
            $model->where('user_id', $userBy);
        }

        $searchable = $this->request->getPost('search_tournament');
        // Apply the filter if the searchable parameter is provided
        if ($searchable) {
            $model->like('searchable', $searchable);
        }

        // Fetch the tournaments
        $tournaments = $model->findAll();

        // Fetch participants for each tournament
        $result_tournaments = [];
        $participantsModel = model('\App\Models\ParticipantModel');
        foreach ($tournaments as &$tournament) {
            $participants = $participantsModel->where('tournament_id', $tournament['id'])->findAll();
            if ($participants) {
                $result_tournaments[] = $tournament;
            }
        }

        // Return the tournaments as a JSON response
        return $this->response->setJSON($result_tournaments);
    }

    public function save()
    {
        $tournamentModel = model('\App\Models\TournamentModel');

        $existing = $tournamentModel->where(['name' => $this->request->getPost('name'), 'user_id' => auth()->user()->id])->findAll();

        if ($existing) {
            return json_encode(['error' => "The same tournament name is existing. Please use another name."]);
        }

        $data = [
            'name' => $this->request->getPost('title'),
            'user_id' => auth()->user()->id,
            'type' => $this->request->getPost('type'),
            'searchable' => $this->request->getPost('title'),
            'archive' => 0
        ];

        $tournament_id = $tournamentModel->insert($data);

        if (!$tournament_id) {
            return json_encode(['error' => "Failed to save the tournament name."]);
        }

        if ($this->request->getPost('setting-toggle')) {
            $musicSettingsModel = model('\App\Models\MusicSettingModel');

            foreach ($this->request->getPost('audioType') as $index => $value) {
                if (isset($this->request->getPost('setting-toggle')[$index]) && $this->request->getPost('setting-toggle')[$index] == 'on') {
                    $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : 'youtube/' . $this->process($this->request->getPost('url')[$index]);
                    $url = ($this->request->getPost('source')[$index] == 'f') ? null : $this->request->getPost('url')[$index];

                    $setting = [
                        'path' => $path,
                        'source' => $this->request->getPost('source')[$index],
                        'tournament_id' => $tournament_id,
                        'user_id' => auth()->user()->id,
                        'type' => $index,
                        'duration' => $this->request->getPost('duration')[$index],
                        'start' => $this->request->getPost('start')[$index],
                        'end' => $this->request->getPost('stop')[$index],
                        'url' => $url
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
        $tournament = $tournamentModel->find($id);
        $tournamentName = $tournament['name'];

        $tournamentModel->update($id, $this->request->getPost());

        $msg = "Tournament [$tournamentName] was updated successfully.";
        if (!is_null($this->request->getPost('archive'))) {
            if ($this->request->getPost('archive')) {
                $msg = "Tournament [$tournamentName] was archived successfully.";
            } else {
                $msg = "Tournament [$tournamentName] was restored successfully.";
            }
        }

        return json_encode(['msg' => $msg, 'data' => $this->request->getPost()]);
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
                $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : 'youtube/' . $this->process($this->request->getPost('url')[$index]);
                
                $setting['path'] = $path;
                $setting['source'] = $this->request->getPost('source')[$index];
                $setting['tournament_id'] = $tournament_id;
                $setting['user_id'] = auth()->user()->id;
                $setting['type'] = $index;
                $setting['duration'] = $this->request->getPost('duration')[$index];
                $setting['start'] = $this->request->getPost('start')[$index];
                $setting['end'] = $this->request->getPost('stop')[$index];
                $setting['url'] = ($this->request->getPost('source')[$index] == 'f') ? null : $this->request->getPost('url')[$index];

                $musicSettingModel->save($setting);
            } else {
                if ($setting) {
                    $musicSettingModel->delete($setting['id']);
                }
            }
        }

        return json_encode(['msg' => "Tournament Music Setting was updated successfully."]);
    }

    public function process($youtubeLink)
    {
        
        parse_str( parse_url( $youtubeLink, PHP_URL_QUERY ), $vars );
        
        if (isset($vars['v'])) {
            $video_id = $vars['v'];
        }

        if (isset($vars['si'])) {
            $video_id = $vars['si'];
        }

        if (file_exists(WRITEPATH . 'uploads/youtube/' . $video_id . '.mp3')) {
            return $video_id . '.mp3';
        }

        $yt = new YoutubeDl();
        $yt->setBinPath('C:\ffmpeg\bin\yt-dlp.exe');
        $collection = $yt->download(
            Options::create()
                ->downloadPath(WRITEPATH . 'uploads/youtube')
                ->extractAudio(true)
                ->audioFormat('mp3')
                ->audioQuality('0') // best
                ->output($video_id)
                ->url($youtubeLink)
        );

        foreach ($collection->getVideos() as $video) {
            if ($video->getError() !== null) {
                echo "Error downloading video: {$video->getError()}.";
            }
        }

        return $video_id . '.mp3';
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
        $data['user_id'] = auth()->user()->id;

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

        /** Notifiy to the users */
        if (isset($users) && count($users)) {
            foreach ($users as $user) {
                $msg = 'Tournament was shared to you.';
                $shared_by = (auth()->user()) ? auth()->user()->id : 0;

                $notification = ['message' => $msg, 'type' => NOTIFICATION_TYPE_FOR_SHARE, 'user_id' => $shared_by, 'user_to' => $user, 'link' => 'tournaments/shared/' . $share['token']];
                $this->notificationService->addNotification($notification);
            }
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
    
    public function bulkDelete()
    {
        $ids = $this->request->getPost('id');
        $tournamentModel = model('\App\Models\TournamentModel');

        /** Alert Message */
        $tournaments = $tournamentModel->whereIn('id', $ids)->findAll();
        $tournament_names = '';
        foreach ($tournaments as $index => $tournament) {
            if ($index == (count($tournaments) - 1)) {
                $tournament_names .= $tournament['name'];
            } else {
                $tournament_names .= $tournament['name'] .',';
            }
        }

        $tournamentModel->delete($ids);

        return json_encode(['status' => 'success', 'msg' => "The following tournaments was deleted successfully.<br/>" . $tournament_names, 'data' => $ids]);
    }

    public function bulkReset()
    {
        $ids = $this->request->getPost('id');
        $bracketModel = model('\App\Models\BracketModel');

        $bracketModel->whereIn('tournament_id', $ids)->delete();

        /** Alert Message */
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournaments = $tournamentModel->whereIn('id', $ids)->findAll();
        $tournament_names = '';
        foreach ($tournaments as $index => $tournament) {
            if ($index == (count($tournaments) - 1)) {
                $tournament_names .= $tournament['name'];
            } else {
                $tournament_names .= $tournament['name'] .',';
            }
        }

        return json_encode(['status' => 'success', 'msg' => "The following tournaments was reseted successfully.<br/>" . $tournament_names, 'data' => $ids]);
    }

    public function bulkUpdate()
    {
        $ids = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $archive = $this->request->getPost('archive');
        $restore = $this->request->getPost('restore');
        $tournamentModel = model('\App\Models\TournamentModel');

        $msg = "The status of following tournaments was updated successfully.<br/>";
        if ($status) {
            $tournamentModel->whereIn('id', $ids)->set(['status' => $status])->update();
        }

        if ($archive) {
            $tournamentModel->whereIn('id', $ids)->set(['archive' => 1])->update();
            $msg = "The following tournaments was archived successfully.<br/>";
        }

        if ($restore) {
            $tournamentModel->whereIn('id', $ids)->set(['archive' => 0])->update();
            $msg = "The following tournaments was restored successfully.<br/>";
        }

        /** Alert Message */
        $tournaments = $tournamentModel->whereIn('id', $ids)->findAll();
        $tournament_names = '';
        foreach ($tournaments as $index => $tournament) {
            if ($index == (count($tournaments) - 1)) {
                $tournament_names .= $tournament['name'];
            } else {
                $tournament_names .= $tournament['name'] .',';
            }
        }

        return json_encode(['status' => 'success', 'msg' => $msg . $tournament_names, 'data' => $ids]);
    }

    public function reuseParticipants() {
        $participantsModel = model('\App\Models\ParticipantModel');

        // Get the user_id parameter from the request
        $tournamentId = $this->request->getPost('id');

        // Apply the filter if the user_id parameter is provided
        if (!$tournamentId) {
            return $this->response->setJSON(['status' => 'error', 'msg' => "Tournament was not selected."]);
        }


        // Fetch the participants
        $participants = $participantsModel->where('tournament_id', $tournamentId)->findAll();
        /** Clear existing participants */
        $participantsModel->where(['tournament_id' => 0, 'user_id' => auth()->user()->id])->delete();

        /** Create new participants list from previous tournaments */
        foreach ($participants as $participant) {
            if ($participant['name']) {
                $newParticipant = new \App\Entities\Participant([
                    'name' => $participant['name'],
                    'user_id' => auth()->user()->id,
                    'tournament_id' => 0,
                    'order' => $participant['order'],
                    'active' => 1
                ]);

                $participantsModel->save($newParticipant);
            }
        }

        // Return the tournaments as a JSON response
        return $this->response->setJSON($participantsModel->where('tournament_id', 0)->findAll());
    }

    public function getParticipants($tournament_id)
    {
        $participantsModel = model('\App\Models\ParticipantModel');
        $participants = [];

        if ($tournament_id) {
            $participants = $participantsModel->where('tournament_id', $tournament_id)->findAll();
        }

        return json_encode($participants);
    }
}