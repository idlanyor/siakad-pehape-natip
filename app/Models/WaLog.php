<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaLog extends Model
{
    protected $fillable = ['phone', 'message', 'event', 'status', 'response'];
}
