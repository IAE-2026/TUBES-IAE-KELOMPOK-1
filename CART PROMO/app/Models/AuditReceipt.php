<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * AuditReceipt Model
 *
 * Stores SOAP audit log receipts from the central IAE audit service.
 *
 * @property int    $id
 * @property string $activity_name
 * @property string $log_content
 * @property string|null $receipt_number
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AuditReceipt extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_name',
        'log_content',
        'receipt_number',
        'status',
    ];
}
