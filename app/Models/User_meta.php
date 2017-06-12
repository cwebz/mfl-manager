<?php

# app/Models/User_meta.php 

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User_meta extends Model
{
    protected $table = 'User_meta';

    protected $primaryKey = 'meta_id';

    protected $fillable = [
        'meta_id',
        'user_id',
        'league_id',
        'team_id',
        'league_name',
        'team_name',
        'created_at',
        'updated_at'
    ];
    
}

?>