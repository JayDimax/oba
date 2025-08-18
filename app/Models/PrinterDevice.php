<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrinterDevice extends Model
{
    use HasFactory;
    protected $fillable = ['agent_id', 'device_ip', 'printer_mac', 'device_name', 'is_online', 'last_seen'];
}
