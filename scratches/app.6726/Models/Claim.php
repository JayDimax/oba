<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'bet_id', 'agent_id', 'claimed_by', 'amount', 'receipt_code', 'claimed_at'
    ];

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
