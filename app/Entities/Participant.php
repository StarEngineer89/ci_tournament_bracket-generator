<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Participant extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at', 'deleted_at'];
    protected $casts   = [];

    protected $attributes = [
        'id'            => null,
        'name'       => null,
        'user_id'  => null,
        'tournament_id' => null,
        'order' => null,
        'active' => null,
        'sessionid' => null
    ];
}