<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollectionStub extends Model
{
    protected $table = 'collection_stub'; // Your pivot table
    public $timestamps = false;

    protected $fillable = [
        'collection_id',
        'stub_id',
        'created_at'
    ];

    /**
     * The collection that owns this stub.
     */
    public function collection()
    {
        return $this->belongsTo(Collection::class, 'collection_id');
    }

    /**
     * All bets that belong to this stub ID.
     */
    public function bets()
    {
        return $this->hasMany(Bet::class, 'stub_id', 'stub_id');
    }
    public function deductions()
{
    return $this->hasMany(Deduction::class, 'collection_stub_id');
}


}
