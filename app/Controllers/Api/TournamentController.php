<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Files\File;
use YoutubeDl\YoutubeDl;
use YoutubeDl\Options;
use App\Services\NotificationService;
use App\Libraries\VoteLibrary;
use App\Libraries\TournamentLibrary;
use Config\UploadConfig;

class TournamentController extends BaseController
{
    protected $notificationService;
    protected $tournamentModel;
    protected $participantModel;
    protected $shareSettingModel;

    public function __construct()
    {
        $this->notificationService = new NotificationService();
        $this->tournamentModel = model('\App\Models\TournamentModel');
        $this->participantModel = model('\App\Models\ParticipantModel');
        $this->shareSettingModel = model('\App\Models\ShareSettingsModel');
    }
    
    public function index()
    {
        //
    }

    public function fetch()
    {
        $tournaments = $this->tournamentModel;
        $searchable = $this->request->getPost('search_tournament');
        $type = $this->request->getPost('type');
        $status = $this->request->getPost('status');
        $created_by = $this->request->getPost('created_by');
        $accessibility = $this->request->getPost('accessibility');
        
        /** Filter the tournaments by my tournament, archived, shared, gallery */
        if ($this->request->getGet('filter') == 'shared') {
            $tournaments = $this->shareSettingModel->tournamentDetails();

            if ($searchable) {
                $tournaments->like(['tournaments.searchable' => $searchable]);
            }

            if ($type) {
                $tournaments->like(['tournaments.type' => $type]);
            }

            if ($status) {
                $tournaments->like(['tournaments.status' => $status]);
            }

            if ($created_by || $created_by == 0) {
                $tournaments->like(['tournaments.user_id' => $created_by]);
            }

            if ($this->request->getGet('type') == 'wh') {
                $tournaments->groupStart();
                $tournaments->whereIn('share_settings.target', [SHARE_TO_EVERYONE, SHARE_TO_PUBLIC]);
                $tournaments->orLike('share_settings.users', strval(auth()->user()->id));
                $tournaments->groupEnd();
                $tempRows = $tournaments->findAll();
                
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
                        if ($tempRow['deleted_at']) {
                            $add_in_list = false;
                        }

                        if ($add_in_list && !in_array($tempRow['token'], $access_tokens)) {
                            $tempRow['access_time'] = convert_to_user_timezone($tempRow['access_time'], user_timezone(auth()->user()->id));
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
            // Get the user_id parameter from the request
            $userBy = $this->request->getPost('user_id');

            // Apply the filter if the user_id parameter is provided
            if ($userBy) {
                $tournaments->where('user_id', $userBy);
            } else {
                $tournaments->where(['visibility' => 1]);
            }

            if ($type) {
                $tournaments->where('type', $type);
            }

            if ($status) {
                $tournaments->where('status', $status);
            }
        
            if ($this->request->getGet('filter') == 'archived') {
                $tournaments->where(['archive' => 1]);
            } else {
                $tournaments->where('archive', 0);
            }

            // Apply the filter if the searchable parameter is provided
            if ($searchable) {
                $tournaments->like('searchable', $searchable);
            }
            
            // Fetch the tournaments
            $tournaments = $tournaments->findAll();
        }

        // Fetch participants and public URL for each tournament
        $result_tournaments = [];
        foreach ($tournaments as &$tournament) {
            $shareSetting = $this->shareSettingModel->where(['tournament_id' => $tournament['id'], 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
            $tournament['public_url'] = '';
            if($shareSetting) {
                $tournament['public_url'] = base_url('/tournaments/shared/') . $shareSetting['token'];
            }

            $tournament['created_at'] = (auth()->user()) ? convert_to_user_timezone($tournament['created_at'], user_timezone(auth()->user()->id)) : $tournament['created_at'];
                
            $participants = $this->participantModel->where('tournament_id', $tournament['id'])->findAll();
            if ($participants) {
                $tournament['participants_count'] = count($participants);
                $result_tournaments[] = $tournament;
            }
        }

        // Return the tournaments as a JSON response
        return $this->response->setJSON($result_tournaments);
    }
    
    public function fetch_gallery()
    {
        $type = $this->request->getPost('type');
        $status = $this->request->getPost('status');
        $created_by = $this->request->getPost('created_by');

        $userModel = model('CodeIgniter\Shield\Models\UserModel');
        $userIdentityModel = model('CodeIgniter\Shield\Models\UserIdentityModel');

        $tournaments = $this->tournamentModel->where(['visibility' => 1]);
        $searchString = '';
        if ($searchString = $this->request->getPost('search_tournament')) {
            $tournaments->like(['tournaments.searchable' => $searchString]);
        }
        
        if ($type) {
            $tournaments->where('type', $type);
        }

        if ($status) {
            $tournaments->where('status', $status);
        }

        if ($created_by || $created_by == 0) {
            $tournaments->where('user_id', $created_by);
        }

        $tournaments = $tournaments->findAll();

        $newTournaments = array();
        $existingHistory = $this->request->getCookie('guest_tournaments');
        $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];
        
        foreach($tournaments as $tournament){
            $temp = $tournament;
            
            $temp['username'] = 'Guest';
            $temp['email'] = '';
            if($tournament['user_id'] > 0){
                $user = $userModel->find($tournament['user_id']);
                $userId = $userIdentityModel->where(['user_id' => $tournament['user_id']])->first();
                $temp['username'] = $user->username;
                $temp['email'] = $userId->secret;
            }

            $participantModel = model('\App\Models\ParticipantModel');
            $temp['participants'] = count($participantModel->where('tournament_id', $tournament['id'])->findAll());

            $sharedTournament = $this->shareSettingModel->where(['tournament_id' => $tournament['id'], 'target' => SHARE_TO_PUBLIC])->orderBy('created_at', 'DESC')->first();
            $temp['public_url'] = ($sharedTournament) ? base_url('/tournaments/shared/') . $sharedTournament['token'] : '';
            
            $participants = $this->participantModel->where('tournament_id', $tournament['id'])->findAll();
            $temp['participants_count'] = 0;
            if ($participants) {
                $temp['participants_count'] = count($participants);
            }
            $newTournaments[] = $temp;
        }

        return $this->response->setJSON($newTournaments);
    }

    public function save()
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $user_id = (auth()->user()) ? auth()->user()->id : 0;

        $db = \Config\Database::connect();
        $dbDriver = $db->DBDriver;
        if (!auth()->user() && $dbDriver === 'MySQLi') {
            $db->query('SET FOREIGN_KEY_CHECKS = 0;');
        }

        $existing = $tournamentModel->where(['name' => $this->request->getPost('title'), 'user_id' => $user_id])->findAll();

        if ($existing) {
            return json_encode(['error' => "The same tournament name is existing. Please use another name."]);
        }

        $data = [
            'name' => $this->request->getPost('title'),
            'user_id' => $user_id,
            'type' => $this->request->getPost('type'),
            'searchable' => $this->request->getPost('title'),
            'archive' => 0,
            'shuffle_enabled' => ($this->request->getPost('shuffle_enabled') == 'on') ? 1 : 0,
            'description' => $this->request->getPost('description'),
            'score_enabled' => ($this->request->getPost('score_enabled') == 'on') ? 1 : 0,
            'score_bracket' => $this->request->getPost('score_bracket'),
            'increment_score' => $this->request->getPost('increment_score'),
            'increment_score_enabled' => ($this->request->getPost('increment_score_enabled') == 'on') ? 1 : 0,
            'increment_score_type' => $this->request->getPost('increment_score_type'),
            'visibility' => ($this->request->getPost('visibility') == 'on') ? 1 : 0,
            'availability' => ($this->request->getPost('availability') && $this->request->getPost('availability') == 'on') ? 1 : 0,
            'available_start' => $this->request->getPost('startAvPicker'),
            'available_end' => $this->request->getPost('endAvPicker'),
            'evaluation_method' => $this->request->getPost('evaluation_method'),
            'voting_accessibility' => $this->request->getPost('voting_accessibility'),
            'voting_mechanism' => $this->request->getPost('voting_mechanism'),
            'max_vote_value' => $this->request->getPost('max_vote_value'),
            'voting_retain' => ($this->request->getPost('voting_retain') == 'on') ? 1 : 0,
            'allow_host_override' => ($this->request->getPost('allow_host_override') == 'on') ? 1 : 0,
            'pt_image_update_enabled' => ($this->request->getPost('pt_image_update_enabled') == 'on') ? 1 : 0,
            'theme' => $this->request->getPost('theme')
        ];
        
        $tournamentData = new \App\Entities\Tournament($data);

        $tournament_id = $tournamentModel->insert($tournamentData);

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
                        'user_id' => $user_id,
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

                    $data['music'][$index] = $setting;
                }
            }
        }

        /**
         * Add the tournament created by guest users to share table
         */
        if(!$user_id || $tournamentData->visibility){
            $shareSettingsModel = model('\App\Models\ShareSettingsModel');

            $shareSetting = $shareSettingsModel->where(['tournament_id' => $tournament_id, 'user_id' => $user_id])->first();
            if(!$shareSetting){
                $config = new \Config\Encryption();
                $token = hash_hmac('sha256', 'tournament_' . $tournament_id . "_created_by_" . $user_id . "_" . time(), $config->key);
                $token = substr($token, 0, 12);
                $shareData = array(
                    'user_id' => $user_id,
                    'tournament_id' => $tournament_id,
                    'target' => 'p',
                    'permission' => SHARE_PERMISSION_VIEW,
                    'token' => $token
                );
                $shareSettingsModel->insert($shareData);
            }
        }
        /** End adding the tournament created by guest users to share table */

        /** Add the tournament Id into the cookie for guest users */
        if (!$user_id) {
            $existingHistory = $this->request->getCookie('guest_tournaments');
            $tournamentHistory = $existingHistory ? json_decode($existingHistory, true) : [];

            $shareSetting = $shareSettingsModel->where(['tournament_id' => $tournament_id, 'user_id' => 0])->first();

            // Add the new tournament to the history
            $tournamentHistory[] = $tournament_id . "_" . $shareSetting['token'];

            // Store updated history in cookies (expire in 1 days)
            $this->response->setCookie('guest_tournaments', json_encode($tournamentHistory), 24 * 60 * 60);
        }
        /** End adding the tournament Id into the cookie for guest users */
        
        if (!auth()->user() && $dbDriver === 'MySQLi') {
            $db->query('SET FOREIGN_KEY_CHECKS = 1;');
        }

        $data['tournament_id'] = $tournament_id;

        return json_encode(['msg' => "Success to save the tournament settings.", 'data' => $data]);
    }

    public function getSettings($id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournament = $tournamentModel->find($id);

        $musicSettingModel = model('\App\Models\MusicSettingModel');

        $settings = $musicSettingModel->where(['tournament_id' => $id])->findAll();
        if ($settings) {
            $new_settings = [];
            foreach ($settings as $setting) {
                $new_settings[$setting['type']] = $setting;
            }

            $settings = $new_settings;
        }

        $settingsBlock = view('tournament/tournament-settings', []);
        $html = view('tournament/music-setting', []);

        return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                            ->setJSON(['msg' => "Tournament was updated successfully.", 'musicSettings' => $settings, 'tournamentSettings' => $tournament, 'settingsBlock' => $settingsBlock, 'html' => $html]);
    }

    public function update($tournament_id)
    {
        $tournamentModel = model('\App\Models\TournamentModel');
        $tournament = $tournamentModel->find(intval($tournament_id));
        
        if ($this->request->getPost('title')) {
            $tournament['name'] = $this->request->getPost('title');
        }
        if ($this->request->getPost('type')) {
            $tournament['type'] = $this->request->getPost('type');
        }
        if ($this->request->getPost('description')) {
            $tournament['description'] = $this->request->getPost('description');
        }
        if ($this->request->getPost('status')) {
            $tournament['status'] = $this->request->getPost('status');
        }
        if ($this->request->getPost('visibility')) {
            $tournament['visibility'] = ($this->request->getPost('visibility') == 'on') ? 1 : 0;

            if ($this->request->getPost('visibility') == 'on') {
                $shareSetting = $this->shareSettingModel->where(['tournament_id' => $tournament_id, 'user_id' => $tournament['user_id']])->first();
                if(!$shareSetting){
                    $config = new \Config\Encryption();
                    $token = hash_hmac('sha256', 'tournament_' . $tournament_id . "_created_by_" . $tournament['user_id'] . "_" . time(), $config->key);
                    $shareData = array(
                        'user_id' => $tournament['user_id'],
                        'tournament_id' => $tournament_id,
                        'target' => 'p',
                        'permission' => SHARE_PERMISSION_VIEW,
                        'token' => $token
                    );
                    $this->shareSettingModel->insert($shareData);
                }
            }
        }
        if ($this->request->getPost('shuffle_enabled')) {
            $tournament['shuffle_enabled'] = ($this->request->getPost('shuffle_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('score_enabled')) {
            $tournament['score_enabled'] = ($this->request->getPost('score_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('score_bracket')) {
            $tournament['score_bracket'] = $this->request->getPost('score_bracket');
        }
        
        if ($this->request->getPost('increment_score_enabled')) {
            $tournament['increment_score_enabled'] = ($this->request->getPost('increment_score_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('increment_score')) {
            $tournament['increment_score'] = $this->request->getPost('increment_score');
        }
        
        if ($this->request->getPost('increment_score_type')) {
            $tournament['increment_score_type'] = $this->request->getPost('increment_score_type');
        }
        
        $scheduleLibrary = new \App\Libraries\ScheduleLibrary();
        if ($this->request->getPost('availability')) {
            $tournament['availability'] = ($this->request->getPost('availability') == 'on') ? 1 : 0;

            if($tournament['availability']){
                $tournament['available_start'] = $this->request->getPost('startAvPicker');
                $tournament['available_end'] = $this->request->getPost('endAvPicker');
            } else {
                $tournament['available_start'] = null;
                $tournament['available_end'] = null;

                $scheduleLibrary->unregisterSchedule($tournament_id);
            }
        }
        
        if ($this->request->getPost('evaluation_method')) {
            $tournament['evaluation_method'] = $this->request->getPost('evaluation_method');
            if($tournament['evaluation_method'] == EVALUATION_METHOD_VOTING){
                $tournament['voting_accessibility'] = $this->request->getPost('voting_accessibility');
                $tournament['voting_mechanism'] = $this->request->getPost('voting_mechanism');
                if ($tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE) {
                    $tournament['max_vote_value'] = $this->request->getPost('max_vote_value');
                } else {
                    $tournament['max_vote_value'] = null;
                }
            } else {
                $tournament['voting_accessibility'] = null;
                $tournament['voting_mechanism'] = null;
                $tournament['max_vote_value'] = null;
            }
            
            if ($this->request->getPost('voting_retain')) {
                $tournament['voting_retain'] = ($this->request->getPost('voting_retain') == 'on') ? 1 : 0;
            }
            if ($this->request->getPost('allow_host_override')) {
                $tournament['allow_host_override'] = ($this->request->getPost('allow_host_override') == 'on') ? 1 : 0;
            }
        }
        
        if ($this->request->getPost('pt_image_update_enabled')) {
            $tournament['pt_image_update_enabled'] = ($this->request->getPost('pt_image_update_enabled') == 'on') ? 1 : 0;
        }

        if ($this->request->getPost('theme')) {
            $tournament['theme'] = $this->request->getPost('theme');
        }

        $tournamentModel->save($tournament);

        /** Schedule to update the rounds by cron */
        if ($tournament['availability'] && $tournament['evaluation_method'] == EVALUATION_METHOD_VOTING && $tournament['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_ROUND) {
            $scheduleLibrary->scheduleRoundUpdate($tournament_id);
        }
        
        /**
         * Update Music Settings
         */
        if ($this->request->getPost('audioType')) {
            $uploadConfig = new UploadConfig();

            $musicSettingModel = model('\App\Models\MusicSettingModel');
            foreach ($this->request->getPost('audioType') as $index => $value) {

                $musicSetting = $musicSettingModel->where(['tournament_id' => $tournament_id, 'type' => $value])->findAll();
                
                if (count($musicSetting)) {
                    $musicSetting = $musicSetting[0];
                } else {
                    $musicSetting = [];
                }

                if (isset($this->request->getPost('setting-toggle')[$index]) && $this->request->getPost('setting-toggle')[$index] == 'on') {
                    if ($index == 2) {
                        $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $uploadConfig->urlVideoUploadPath . $this->process($this->request->getPost('url')[$index], 'video');
                    } else {
                        $path = ($this->request->getPost('source')[$index] == 'f') ? $this->request->getPost('file-path')[$index] : $uploadConfig->urlAudioUploadPath . $this->process($this->request->getPost('url')[$index]);
                    }
                    
                    $musicSetting['path'] = $path;
                    $musicSetting['source'] = $this->request->getPost('source')[$index];
                    $musicSetting['tournament_id'] = $tournament_id;
                    $musicSetting['user_id'] = auth()->user()->id;
                    $musicSetting['type'] = $index;
                    $musicSetting['duration'] = $this->request->getPost('duration')[$index];
                    $musicSetting['start'] = $this->request->getPost('start')[$index];
                    $musicSetting['end'] = $this->request->getPost('stop')[$index];
                    $musicSetting['url'] = ($this->request->getPost('source')[$index] == 'f') ? null : $this->request->getPost('url')[$index];
                    
                    $musicSettingModel->save($musicSetting);
                } else {
                    if ($musicSetting) {
                        $musicSettingModel->delete($musicSetting['id']);
                    }
                }
            }
        }

        $tournamentName = $tournament['name'];
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

    public function process($youtubeLink, $type = 'audio')
    {   
        $uploadConfig = new UploadConfig();
        
        parse_str( parse_url( $youtubeLink, PHP_URL_QUERY ), $vars );
        
        if (isset($vars['v'])) {
            $video_id = $vars['v'];
        }

        if (isset($vars['si'])) {
            $video_id = $vars['si'];
        }

        $yt = new YoutubeDl();
        $yt->setBinPath('C:\ffmpeg\bin\yt-dlp.exe');
        if ($type == 'audio') {
            if (file_exists(WRITEPATH . "uploads/$uploadConfig->urlAudioUploadPath/" . $video_id . '.mp3')) {
                return $video_id . '.mp3';
            }

            $collection = $yt->download(
                Options::create()
                    ->downloadPath(WRITEPATH . "uploads/$uploadConfig->urlAudioUploadPath")
                    ->extractAudio(true)
                    ->audioFormat('mp3')
                    ->audioQuality('0') // best
                    ->output($video_id)
                    ->url($youtubeLink)
                    ->cookies('C:\ffmpeg\www.youtube.com_cookies.txt')
            );
        } else {
            if (file_exists(WRITEPATH . "uploads/$uploadConfig->urlVideoUploadPath/" . $video_id . '.webm')) {
                return $video_id . '.webm';
            }

            $collection = $yt->download(
                Options::create()
                    ->downloadPath(WRITEPATH . "uploads/$uploadConfig->urlVideoUploadPath")
                    ->output($video_id)
                    ->url($youtubeLink)
                    ->cookies('C:\ffmpeg\www.youtube.com_cookies.txt')
            );
        }

        foreach ($collection->getVideos() as $video) {
            if ($video->getError() !== null) {
                echo "Error downloading video: {$video->getError()}.";
            }
        }

        return $video_id . '.mp3';
    }

    public function delete($id)
    {
        $tournamentLibrary = new TournamentLibrary();
        $tournamentLibrary->deleteTournament($id);

        return json_encode(['msg' => "Tournament was deleted successfully."]);
    }

    public function upload()
    {
        $validationRule = [
            'audio' => [
                'label' => 'Audio File',
                'rules' => [
                    'uploaded[audio]',
                    'mime_in[audio,audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi]',
                    'max_size[audio,104857600]', // Limits file size to 100MB (102400 KB)
                ],
                'errors' => [
                    'uploaded' => 'Please upload a file.',
                    'mime_in' => 'The uploaded file must be a valid audio format.',
                    'max_size' => 'The file size must not exceed 10MB.',
                ],
            ],
        ];
        
        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];
            
            return $this->response->setJSON($data);
        }

        $uploadConfig = new UploadConfig();

        $audio = $this->request->getFile('audio');

        if (!$audio->hasMoved()) {
            $filepath = $audio->store($uploadConfig->localAudioUploadPath);

            $data = ['uploaded_fileinfo' => new File($filepath), 'path' => $filepath];

            return $this->response->setJSON($data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return $this->response->setJSON($data);
    }

    public function uploadVideo()
    {
        $validationRule = [
            'video' => [
                'label' => 'Video File',
                'rules' => [
                    'uploaded[video]',
                    'mime_in[video,video/mp4]',
                    'max_size[video, 524288000]', // Limits file size to 500MB
                ],
                'errors' => [
                    'uploaded' => 'Please upload a file.',
                    'mime_in' => 'The uploaded file must be a valid video format.',
                    'max_size' => 'The file size must not exceed 500MB.',
                ],
            ],
        ];
        
        if (!$this->validateData([], $validationRule)) {
            $data = ['errors' => $this->validator->getErrors()];
            
            return $this->response->setJSON($data);
        }

        $uploadConfig = new UploadConfig();

        $video = $this->request->getFile('video');

        if (!$video->hasMoved()) {
            $filepath = $video->store($uploadConfig->localVideoUploadPath);

            $data = ['uploaded_fileinfo' => new File($filepath), 'path' => $filepath];

            return $this->response->setJSON($data);
        }

        $data = ['errors' => 'The file has already been moved.'];

        return $this->response->setJSON($data);
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

                $setting['created_at'] = convert_to_user_timezone($setting['created_at'], user_timezone(auth()->user()->id));
                $setting['updated_at'] = convert_to_user_timezone($setting['updated_at'], user_timezone(auth()->user()->id));

                $settings_with_users[] = $setting;
            }
        }
        
        $config = new \Config\Encryption();
        $token = hash_hmac('sha256', 'tournament_' . $tournament_id . '_created_by_' . auth()->user()->id . '_' . time(), $config->key);
        $token = substr($token, 0, 12);

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
            
            $tournamentModel = model('\App\Models\TournamentModel');
            $tournament = $tournamentModel->find($data['tournament_id']);
            $tournamentName = $tournament['name'];
        
            foreach ($users as $user) {
                $msg = "Tournament \"$tournamentName\" was privately shared with you.";
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
        $roundSettingsModel = model('\App\Models\TournamentRoundSettingsModel');

        $history = $logActionsModel->getLogs()->where('tournament_id', $tournament_id)->findAll();

        $data = [];
        $type = null;
        $description = null;
        if ($history && count($history)) {
            foreach ($history as $row) {
                
                if ($row['action'] == BRACKET_ACTIONCODE_CLEAR) {
                    $tournamentModel = model('\App\Models\TournamentModel');
                    $tournament = $tournamentModel->find($tournament_id);
                    $tournamentName = $tournament['name'];
                    $type = 'Reset';
                    $description = "Tournament \"$tournamentName\" was reset.";
                } else {
                    $params = json_decode($row['params']);
                    $participants = [];
                    if (isset($params->participants)) {
                        $participants = $params->participants;
                    }

                    $roundSetting = $roundSettingsModel->where(['tournament_id' => $row['tournament_id'], 'round_no' => $params->round_no])->first();
                    
                    $roundName = ($roundSetting) ? $roundSetting['round_name'] : "round $params->round_no";
                    if ($row['action'] == BRACKET_ACTIONCODE_MARK_WINNER) {
                        $type = 'Mark Winner';
                        $description = "Participant \"$participants[0]\" in bracket #$params->bracket_no marked as a winner in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_UNMARK_WINNER) {
                        $type = 'Unmark Winner';
                        $description = "Participant \"$participants[0]\" in bracket #$params->bracket_no unmarked winner in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_CHANGE_PARTICIPANT) {
                        $type = 'Change Participant';
                        $description = "Participant \"$participants[0]\" in bracket #$params->bracket_no changed to the following Participant: \"$participants[1]\" in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_ADD_PARTICIPANT) {
                        $type = 'Add Participant';
                        $description = "Participant \"$participants[0]\" added in bracket #$params->bracket_no in $roundName";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_DELETE) {
                        $type = 'Delete Bracket';
                        $description = "Bracket #$params->bracket_no containing participants [\"$participants[0]\", \"$participants[1]\"] in $roundName deleted";
                    }

                    if ($row['action'] == BRACKET_ACTIONCODE_VOTE) {
                        $type = 'Voting';
                        $description = "Participant \"$participants[0]\" in bracket #$params->bracket_no voted in $roundName";
                    }
                }

                $data[] = [
                    'name' => $row['username'],
                    'type' => $type,
                    'description' => $description,
                    'time' => convert_to_user_timezone($row['updated_at'], user_timezone(auth()->user()->id))
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
        if (auth()->user()) {
            $user_id = auth()->user()->id;
        } else {
            $user_id = 0;
        }

        if ($user_id) {
            $participantsModel->where(['tournament_id' => 0, 'user_id' => $user_id])->delete();
        } else {
            $participantsModel->where(['tournament_id' => 0, 'sessionid' => $this->request->getPost('hash')])->delete();
        }

        /** Create new participants list from previous tournaments */
        foreach ($participants as $participant) {
            if ($participant['name']) {
                $newParticipant = new \App\Entities\Participant([
                    'name' => $participant['name'],
                    'user_id' => $user_id,
                    'tournament_id' => 0,
                    'order' => $participant['order'],
                    'active' => 1,
                    'sessionid' => $this->request->getPost('hash')
                ]);

                $participantsModel->save($newParticipant);
            }
        }

        if ($user_id) {
            $participants = $participantsModel->where(['tournament_id' => 0, 'user_id' => $user_id])->findAll();
        } else {
            $participants = $participantsModel->where(['tournament_id' => 0, 'sessionid' => $this->request->getPost('hash')])->findAll();
        }

        // Return the tournaments as a JSON response
        return $this->response->setJSON($participants);
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

    public function saveVote()
    {
        // Check if it's an AJAX request
        if ($this->request->isAJAX()) {
            $voteData = $this->request->getPost(); // Get the posted data

            $voteModel = model('\App\Models\VotesModel');
            $participantsModel = $this->participantModel;
            
            $bracketModel = model('\App\Models\BracketModel');
            $bracket = $bracketModel->find($voteData['bracket_id']);
            $nextBracket = $bracketModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame']])->findAll();
            if (count($nextBracket) == 1) {
                $nextBracket = $nextBracket[0];
            } else {
                $nextBracket = $bracketModel->where(['tournament_id' => $bracket['tournament_id'], 'bracketNo' => $bracket['nextGame'], 'is_double' => $bracket['is_double']])->first();
            }

            // Validation (optional, based on your form fields)
            $validation = \Config\Services::validation();
            $validation->setRules([
                'user_id' => 'required|integer',
                'tournament_id' => 'required|integer',
                'participant_id' => 'required|integer',
                'bracket_id' => 'required|integer',
                'round_no' => 'required|integer'
            ]);
            if (auth()->user()) {
                $voteData['user_id'] = auth()->user()->id;
            } else {
                $voteData['user_id'] = 0;
            }

            if (!$validation->run($voteData)) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                      ->setJSON(['status' => 'error', 'message' => $validation->getErrors()]);
            }

            $saveData = $voteData;
            // Update the bracket ID on knockout final
            $tournament_settings = $this->tournamentModel->find($voteData['tournament_id']);
            if ($tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $bracket['final_match'] == 1 && $nextBracket) {
                $saveData['bracket_id'] = $nextBracket['id'];
            }
            // End update the bracket ID on knockout final

            //Check if there is the data saved before
            if (auth()->user()) {
                $prevVote = $voteModel->where(['user_id' => auth()->user()->id, 'tournament_id' => $saveData['tournament_id'], 'bracket_id' => $saveData['bracket_id']])->first();
            } else {
                $prevVote = $voteModel->where(['uuid' => $saveData['uuid'], 'tournament_id' => $saveData['tournament_id'], 'bracket_id' => $saveData['bracket_id']])->first();
            }

            if ($prevVote) {
                if ($prevVote['participant_id'] == $voteData['participant_id']) {
                    return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                      ->setJSON(['status' => 'error', 'message' => 'You have already voted for this participant.']);
                } else {
                    $prevVote['participant_id'] = $voteData['participant_id'];
                }

                $saveData = $prevVote;
            }

            // Check if participant is second one
            $currentTeams = json_decode($bracket['teamnames'], true);
            $isDouble = null;
            foreach ($currentTeams as $ct) {
                if ($ct && $ct['id'] == $voteData['participant_id'] && isset($ct['is_double'])) {
                    $isDouble = 1;
                }
            }
            if ($isDouble) {
                $saveData['is_double'] = 1;
            } else {
                $saveData['is_double'] = null;
            }
            // End check if participant is second one

            $db = \Config\Database::connect();
            $dbDriver = $db->DBDriver;
            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 0;');
            }
            
            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = OFF');
            }

            // Save to database
            if ($voteModel->save($saveData)) {
                /** Save the record to actions log table */
                $logActionsModel = model('\App\Models\LogActionsModel');
                $insert_data = ['tournament_id' => $saveData['tournament_id'], 'action' => BRACKET_ACTIONCODE_VOTE];
                if (auth()->user()) {
                    $insert_data['user_id'] = auth()->user()->id;
                } else {
                    $insert_data['user_id'] = 0;
                }
                
                $data = [];
                $data['bracket_no'] = $saveData['bracket_id'];
                $data['round_no'] = $saveData['round_no'];
                $participant = $participantsModel->find($saveData['participant_id']);
                $data['participants'] = [$participant['name']];
                $insert_data['params'] = json_encode($data);

                $logActionsModel->insert($insert_data);
                
                /** Mark Participant win if max vote count reaches */
                $voteData['is_double'] = $isDouble;
                $vote_max_limit = intval($tournament_settings['max_vote_value']);
                $search_params = array_diff_key($voteData, array('bracket_id' => true, 'user_id' => true, 'uuid' => true));
                $votes_in_round = $voteModel->where($search_params)->findAll();
                
                if ($tournament_settings['evaluation_method'] == EVALUATION_METHOD_VOTING && $tournament_settings['voting_retain']) {
                    $search_params = array_diff_key($search_params, array('round_no' => true));
                }

                if ($nextBracket && is_null($nextBracket['nextGame'])) {
                    $search_params = array_diff_key($search_params, array('is_double' => true));
                }
                
                /** Get Votes count in a round */
                $votes = $voteModel->where($search_params)->findAll();
                $saveData['votes'] = count($votes);
                if ($tournament_settings['voting_mechanism'] == EVALUATION_VOTING_MECHANISM_MAXVOTE && count($votes_in_round) >= $vote_max_limit) {
                    $voteLibrary = new VoteLibrary();
                    $result = $voteLibrary->markWinParticipant($voteData);
                    
                    if ($tournament_settings && $tournament_settings['type'] == TOURNAMENT_TYPE_KNOCKOUT && $nextBracket['knockout_final']) {
                        $saveData['final_win'] = true;
                    }

                    if ($tournament_settings && $tournament_settings['type'] != TOURNAMENT_TYPE_KNOCKOUT && $nextBracket['final_match'] == 1) {
                        $saveData['final_win'] = true;
                    }
                }
                
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                      ->setJSON(['status' => 'success', 'message' => 'Vote saved successfully', 'data' => $saveData]);
            } else {
                return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                      ->setJSON(['status' => 'error', 'message' => 'Failed to save vote']);
            }
            
            if (!auth()->user() && $dbDriver === 'MySQLi') {
                $db->query('SET FOREIGN_KEY_CHECKS = 1;');
            }
            
            if (!auth()->user() && $dbDriver === 'SQLite3') {
                $db->query('PRAGMA foreign_keys = ON');
            }

        }

        // If not an AJAX request, return a 403 error
        return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                              ->setJSON(['status' => 'error', 'message' => 'Invalid request']);
    }
    public function exportLogs()
    {
        $tournament_id = $this->request->getGet('tid');
        $actionHistory = $this->getActionHistory($tournament_id);
        $data = json_decode($actionHistory, true);
        $actionHistory = $data['history'];

        $filename = 'tournaments_' . date('Ymd') . '.csv';

        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=\"$filename\"");

        $output = fopen('php://output', 'w');

        // Add the CSV column headers
        fputcsv($output, ['No', 'User', 'Action Type', 'Description', 'Time']);

        // Fetch the data and write it to the CSV
        foreach ($actionHistory as $index => $action) {
            $username = ($action['name']) ? $action['name'] : 'Guest';

            fputcsv($output, [
                $index + 1,
                $username,
                $action['type'],
                $action['description'],
                $action['time']
            ]);
        }

        fclose($output);
        exit;
    }

}