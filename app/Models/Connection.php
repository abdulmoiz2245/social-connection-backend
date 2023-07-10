<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Awssat\ViewGenerator\ViewGenerator;

class Connection extends Model
{
    use HasFactory;
    protected $fillable = ['sender_id', 'receiver_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

}
