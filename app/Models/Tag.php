<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps  = false;
    protected $table = "tag"; // or change name in sql
    protected $fillable = ['name'];
    use HasFactory;

    public function questions() {
        return $this->belongsToMany(Question::class,'question_tag', 'question_id','tag_id');
    }

    public function followers(){
        return $this->belongsToMany(User::class, 'follow_tag', 'user_id', 'tag_id');
    }

}
