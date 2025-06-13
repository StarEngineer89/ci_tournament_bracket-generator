<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Bracket extends Entity
{
    protected $datamap = [];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts = [];
    protected $attributes = [
        'tournament_id' => null,
        'bracketNo' => null,
        'bye' => null,
        'lastGames' => null,
        'nextGame' => null,
        'roundNo' => null,
        'teamnames' => null,
        'winner' => null,
        'final_match' => null,
        'win_by_host' => null,
        'is_double' => null,
        'knockout_final' => null,
        'user_id' => null,
    ];
}