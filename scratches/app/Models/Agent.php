<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'phone',
        'status',
        'role',
        'balance',
    ];

    protected $casts = [
        'balance' => 'float',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'agent_id');
    }
    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');


    }
    public function collections()
    {
        return $this->hasMany(Collection::class);
    }
    // app/Models/Agent.php

public function deductions()
{
    return $this->hasMany(Deduction::class, 'agent_id');
}

public function commission()
{
    return $this->hasOne(AgentCommission::class);
}


}
