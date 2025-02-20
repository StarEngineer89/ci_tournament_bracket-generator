<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class TournamentPreservedFilter implements FilterInterface
{
    /**
     * Do whatever processing this filter needs to do.
     * By default it should not return anything during
     * normal execution. However, when an abnormal state
     * is found, it should return an instance of
     * CodeIgniter\HTTP\Response. If it does, script
     * execution will end and that Response will be
     * sent back to the client, allowing for error pages,
     * redirects, etc.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return RequestInterface|ResponseInterface|string|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        //
    }

    /**
     * Allows After filters to inspect and modify the response
     * object as needed. This method does not allow any way
     * to stop execution of other after filters, short of
     * throwing an Exception or Error.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return ResponseInterface|void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if(isset($_COOKIE['guest_tournaments']) && auth()->user()){
            $tournament_ids_array = json_decode($_COOKIE['guest_tournaments'], true);
            $tournament_ids = [];
            if ($tournament_ids_array) {
                foreach ($tournament_ids_array as $item) {
                    $split_data = explode('_', $item);
                    $tournament_ids[] = $split_data[0];
                }
            }
            
            $shareSettingsModel = model('\App\Models\ShareSettingsModel');
            $tournamentModel = model('\App\Models\TournamentModel');
            $participantModel = model('\App\Models\participantModel');
            $sharedTournaments = $shareSettingsModel->where(['user_id' => 0])->whereIn('tournament_id', $tournament_ids)->findAll();
            if($sharedTournaments){
                foreach ($sharedTournaments as $sharedTournament) {
                    $sharedTournament['user_id'] = auth()->user()->id;
                    $sharedTournament['uuid'] = null;
                    $shareSettingsModel->save($sharedTournament);

                    $tournament = $tournamentModel->find($sharedTournament['tournament_id']);
                    $tournament['user_id'] = auth()->user()->id;
                    $tournamentModel->save($tournament);

                    $participantModel->where(['tournament_id' => $tournament['id']])->set('user_id', auth()->user()->id)->update();

                    $bracketModel = model('\App\Models\BracketModel');
                    $bracketModel->where(['tournament_id' => $sharedTournament['tournament_id'], 'user_id' => 0])->set('user_id', auth()->user()->id)->update();
                }
            }

            $response->deleteCookie('guest_tournaments');
        }
    }
}