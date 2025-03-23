<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoteNotification extends Model
{
    use HasFactory;

    protected $table = 'vote_notification';

    protected $fillable = [
        'notified',
        'viewed',
        'date',
        'voter',
        'post_id',
    ];

    public $timestamps = false;

    public function user(){
        return $this->belongsTo(User::class, 'notified');
    }

    public function vote(){
        return $this->belongsTo(Vote::class, ['voter', 'post_id'], ['user_id', 'post_id']);
    }


    public function markViewed(){
        $this->viewed = true;
        $this->save();
    }
}

