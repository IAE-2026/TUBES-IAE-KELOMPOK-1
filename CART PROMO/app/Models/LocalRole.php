<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocalRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'local_roles';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sso_email',
        'role',
    ];
}
