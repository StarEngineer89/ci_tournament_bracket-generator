<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class TournamentParticipant extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];
    protected $attributes = [
        'id'            => null,
        'tournament_id'       => null,
        'participant_id'  => null,
        'order' => null,
    ];
}