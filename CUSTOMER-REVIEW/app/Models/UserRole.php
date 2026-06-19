<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    
    protected $fillable = [
        'email',
        'sso_user_id',
        'role',
        'jwt_token',
    ];
}
