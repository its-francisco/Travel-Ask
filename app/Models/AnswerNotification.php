<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnswerNotification extends Model
{
    use HasFactory;

    protected $table = 'answer_notification';

    protected $fillable = [
        'notified',
        'viewed',
        'date',
        'answer_id',
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class, 'notified');
    }

    public function answer()
    {
        return $this->belongsTo(Answer::class, 'answer_id', 'post_id');
    }

}
