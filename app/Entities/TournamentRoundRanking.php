<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TournamentRoundRanking extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    protected $attributes = [
        'id'            => null,
        'tournament_id'  => null,
        'round_no' => null,
        'bracket_id' => null,
        'participant_id' => null,
        'ranking' => null,
        'score' => null,
        'time' => null,
        'created_by' => null
    ];
}