<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;
    protected $fillable = ['sender_id', 'receiver_id', 'status'];

    public function receiver()
    {
        // Return the User model  using the receiver_id column as the foreign key
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function sender()
    {
        // Return the User model  using the sender_id column as the foreign key
        return $this->belongsTo(User::class, 'sender_id');
    }
}
