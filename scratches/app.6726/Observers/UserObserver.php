<?php

namespace App\Observers;

// app/Observers/UserObserver.php

use App\Models\User;
use App\Models\Agent;
use App\Models\Cashier;

class UserObserver
{
    public function created(User $user)
    {
        if ($user->role === 'agent') {
            Agent::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'agent_code' => $user->agent_code,
            ]);
        } elseif ($user->role === 'cashier') {
            Cashier::create([
                'user_id' => $user->id,
                'name' => $user->name,
                'cashier_code' => $user->agent_code,
            ]);
        }
    }
    
}
