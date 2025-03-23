<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps  = false;
    protected $table = "city";

    use HasFactory;

    public function questions() {
        return $this->hasMany(Question::class);
    } 

    public function country() {
        return $this->belongsTo(Country::class);
    }
    public function events() {
        return $this->hasMany(Event::class);
    }
}
