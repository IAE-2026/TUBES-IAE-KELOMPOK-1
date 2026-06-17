<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'receipt_number',
        'activity_name',
        'log_content',
    ];
}
