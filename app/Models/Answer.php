<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Post
{
    public $timestamps  = false;
    protected $table = "answer";
    protected $primaryKey = 'post_id';

    use HasFactory;

    protected $fillable = [
        'post_id',
        'question_id',
        'correct'
    ];

    protected $hidden = ['deleted'];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    } 
    public function content()
    {
        return $this->post()->first()->content;
    }

    public function question() {
        return $this->belongsTo(Question::class,'question_id', 'post_id');
    }

    public function comments() {
        return $this->hasManyThrough(Comment::class, Post::class, 'id', 'post_id', 'post_id', 'id');
    }

    public function markAsCorrect(){
        $this->correct = true;
        $this->save();
    }
}