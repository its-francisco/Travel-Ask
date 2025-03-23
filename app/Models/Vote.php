<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;



class Vote extends Model
{
    use HasFactory;
    


    protected $table = 'vote';
    public $timestamps = false;
    public $incrementing = false; 
    protected $primaryKey = ['user_id', 'post_id'];

    protected $fillable = [
        'user_id',
        'post_id',
        'vote',
        'date',
    ];


    public function voter(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(){
        return $this->belongsTo(Post::class, 'post_id');
    } 
}
