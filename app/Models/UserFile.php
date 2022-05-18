<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'fileID',
        'original_name',
        'name',
        'extension',
        'isPasswordProtected',
        'password',
        'isDayExpired',
        'expired_at',
        'isDownloadExpired',
        'download_count',
        'download_expired_at',
    ];


}
