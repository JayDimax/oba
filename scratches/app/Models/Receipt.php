<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Receipt extends Model
{
    protected $fillable = [
        'stub_id',
        'agent_id',
        'cashier_id',
        'total_amount',
        'status',
        'remitted_at',
        'reference_code',
        'batch_id',
    ];

    protected $casts = [
        'remitted_at' => 'datetime',
    ];

    /**
     * Relationships
     */

    public function bet()
    {
        return $this->belongsTo(Bet::class, 'stub_id', 'stub_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function batch()
    {
        return $this->belongsTo(RemittanceBatch::class, 'batch_id');
    }

    /**
     * Boot method to hook into model events
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->reference_code)) {
                $receipt->reference_code = self::generateUniqueReferenceCode();
            }
        });
    }

    /**
     * Generate a unique reference code with prefix TXN- + 8 uppercase hex chars
     *
     * @return string
     */
    protected static function generateUniqueReferenceCode()
    {
        do {
            $code = 'TXN-' . strtoupper(substr(md5(Str::random(16)), 0, 8));
        } while (self::where('reference_code', $code)->exists());

        return $code;
    }
}
