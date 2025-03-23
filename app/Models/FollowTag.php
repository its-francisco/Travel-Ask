<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowTag extends Model
{
    use HasFactory;

    protected $table = 'follow_tag';
    public $timestamps = false;

    public $incrementing = false; 
    protected $primaryKey = ['user_id', 'tag_id'];

    protected $fillable = [
        'user_id',
        'tag_id',
    ];
}