<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RemittanceBatch extends Model
{
    use HasFactory;

    protected $fillable = [
    'agent_id',
    'cashier_id',
    'total_amount',
    'status',
    'submitted_at',
    ];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function receipts()
    {
        return $this->hasMany(Receipt::class, 'batch_id');
    }

}
