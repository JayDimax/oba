<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentBalance extends Model
{
    protected $fillable = [
        'agent_id', 'amount', 'type', 'note', 'cashier_id', 'remittance_batch_id'
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function cashier()
    {
        return $this->belongsTo(Cashier::class);
    }

    public function remittanceBatch()
    {
        return $this->belongsTo(RemittanceBatch::class);
    }
}

