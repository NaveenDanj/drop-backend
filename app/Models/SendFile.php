<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_user',
        'to_user',
        'to_email',
        'file_id',
        'subject',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function file()
    {
        return $this->belongsTo('App\Models\UserFile');
    }

    public function getFromUser()
    {
        return $this->belongsTo('App\Models\User', 'from_user');
    }

}
