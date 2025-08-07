<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

protected $fillable = [
    'stub_id',
    'game_type',
    'game_draw',
    'game_date',   
    'bet_number',
    'amount',
    'agent_id',
    'multiplier',
    'commission',
    'commission_base',
    'commission_bonus',
    'is_winner',
    'winnings',
];
    
    
    public function agent()
    {
        return $this->belongsTo(Agent::class,'agent_id');
    }

    public function betAgent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function claim()
    {
        return $this->hasOne(Claim::class);
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class, 'stub_id', 'stub_id');
    }

    public function getIsPaidAttribute()
    {
        return $this->receipt()->exists();
    }

    public function remittance()
    {
        return $this->belongsTo(Remittance::class, 'remittance_id');
    }

    public function isRemitted()
    {
        return $this->remittance()->exists();
    }

    public function getIsClaimedAttribute()
    {
        return $this->claim !== null;
    }
//multiplier for the admin side
    public function multiplier()
{
    return $this->belongsTo(Multiplier::class, 'game_type', 'game_type');
}

    // Display-only dynamic multiplier from multipliers table// winning bets function in agent
public function getDisplayMultiplierAttribute()
{
    return \App\Models\Multiplier::where('game_type', $this->game_type)
        ->value('multiplier') ?? 0;
}



}
