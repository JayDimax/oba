<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id', 'collection_date', 'gross', 'payouts',
        'deductions', 'net_remit', 'gcash_reference', 'proof_file',
        'is_remitted', 'remarks','verified_at','verified_by', 'status',
    ];

    public function agent() {
        return $this->belongsTo(User::class, 'agent_id');
    }
    
    // app/Models/Collection.php

public function stubs()
{
    return $this->hasMany(CollectionStub::class);
}

public function collectionStubs()
{
    return $this->hasMany(CollectionStub::class, 'collection_id');
}
public function deductions()
{
    return $this->hasMany(Deduction::class);
}
public function verifiedBy()
{
    return $this->belongsTo(User::class, 'verified_by');
}

    
}
