<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    protected $table = 'labs';
    protected $fillable = ['nama', 'deskripsi', 'author'];
}
