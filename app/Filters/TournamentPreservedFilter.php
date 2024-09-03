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
        //log_message('debug', "TournamentPreservedFilter is working");
        if(isset($_COOKIE['tournament_id']) && auth()->user()){
            $tournament_id = $_COOKIE['tournament_id'];
            $shareSettingsModel = model('\App\Models\ShareSettingsModel');
            $tournamentModel = model('\App\Models\TournamentModel');
            $sharedTournament = $shareSettingsModel->where(['tournament_id'=> $tournament_id, 'user_id' => 0])->first();
            if($sharedTournament){
                $sharedTournament['user_id'] = auth()->user()->id;
                $shareSettingsModel->save($sharedTournament);

                $tournament = $tournamentModel->find($tournament_id);
                $tournament['user_id'] = auth()->user()->id;
                $tournamentModel->save($tournament);

                $bracketModel = model('\App\Models\BracketModel');
                $bracketModel->where(['tournament_id'=> $tournament_id, 'user_id'=> 0])->set('user_id', auth()->user()->id)->update();
            }

            setcookie('tournament_id', '', time()-3600);
        }
    }
}