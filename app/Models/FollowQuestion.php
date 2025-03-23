<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowQuestion extends Model
{
    use HasFactory;

    protected $table = 'follow_question';
    public $timestamps = false;
    public $incrementing = false; 
    protected $primaryKey = ['user_id', 'question_id'];
    
    protected $fillable = [
        'user_id',
        'question_id',
    ];
}