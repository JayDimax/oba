<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'blocked_draw',
    ];

    // Relation to User (Agent)
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
