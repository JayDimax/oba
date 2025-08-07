<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'agent_code',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function generateAgentCode($region = 'DVO')
{
    $count = self::where('role', 'agent')->count() + 1;
    return strtoupper($region . '-AGT' . str_pad($count, 4, '0', STR_PAD_LEFT));
}

public function agent()
{
    return $this->hasOne(Agent::class);
}


public function bets()
{
    return $this->hasMany(Bet::class , 'agent_id', 'id');
}

public function commissions()
{
    return $this->hasMany(AgentCommission::class, 'agent_id');
}

// The agent's assigned cashier
public function cashier()
{
    return $this->belongsTo(User::class, 'cashier_id');
}

// The cashier’s assigned agents (reverse)
public function assignedAgents()
{
    return $this->hasMany(User::class, 'cashier_id');
}
// Get only remitted bets
public function remittedBets()
{
    return $this->bets()->whereNotNull('remittance_id');
}
public function collections()
{
    return $this->hasMany(Collection::class, 'agent_id');
}

public function agents()
{
    return $this->hasMany(User::class, 'cashier_id');
}
public function agentBlocks()
{
    return $this->hasMany(AgentBlock::class, 'agent_id');
}


}