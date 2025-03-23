<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class Post extends Model
{

    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = "post"; // or change name in sql

    use HasFactory;

    protected $fillable = [
        'content',
        'user_id',
        'date'
    ];

    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    } 

    public function votes(){
        return $this->hasMany(Vote::class, 'post_id');
    }

    public function upvotesCount(){
        return $this->votes()->where('vote', 'Up')->count();
    }
    public function downvotesCount(){
        return $this->votes()->where('vote', 'Down')->count();
    }

    public static function allSortedByVotes(){
        // get all posts sorted
        return self::withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();
    }

}


