<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Database extends Model
{
    //

    protected $fillable = [
        'driver',
        'host',
        'port',
        'database',
        'username',
        'password'
    ];
}
