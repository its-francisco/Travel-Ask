<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;



class Question extends Model
{

    public $timestamps  = false;
    protected $table = "question";
    protected $primaryKey = 'post_id';
    protected $fillable = [
        'title',
        'post_id', 
        'country_id', 
        'city_id'
    ];

    use HasFactory;

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    } 

    public function content()
    {
        return $this->post()->first()->content;
    }

    public function country() {
        return $this->belongsTo(Country::class);
    } 

    public function city() {
        return $this->belongsTo(City::class);
    } 

    public function answers() {
        return $this->hasMany(Answer::class, 'question_id', 'post_id');
    }

    public function comments() {
        return $this->hasManyThrough(Comment::class, Post::class, 'id', 'post_id', 'post_id', 'id');
    }

    public function tags() {
        return $this->belongsToMany(Tag::class, 'question_tag', 'question_id','tag_id');
    }

    public function votes(){
        return $this->hasManyThrough(Vote::class, Post::class, 'id', 'post_id', 'post_id', 'id');
    }


    // note: you can use votes_count as the count of votes!
    public static function allSortedByVotes(){
        // get all questions sorted
        return self::withCount('votes')
            ->orderBy('votes_count', 'desc')
            ->get();
    }

    public function hasCorrectAnswer(){
        return $this->answers()->where('correct', true)->exists();
    }

}
