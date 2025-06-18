<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Topology extends Model
{
    protected $fillable = ['nodes', 'connections', 'power'];

    protected $casts = [
        'nodes' => 'array',
        'connections' => 'array',
        'power' => 'float',
    ];
}
