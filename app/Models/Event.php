<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public $timestamps  = false;
    protected $table = "event";
    protected $fillable = [
        'city_id',
        'start_date',
        'end_date',
        'name',
        'description',
    ];
    use HasFactory;
    public function city() {
        return $this->belongsTo(City::class);
    } 
}
