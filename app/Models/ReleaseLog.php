<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReleaseLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'release_date',
        'version',
        'release_log'
    ];
}
