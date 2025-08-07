<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    use HasFactory;
    protected $fillable = ['agent_id', 'game_type', 'commission_percent'];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
