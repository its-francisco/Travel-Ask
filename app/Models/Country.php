<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    
    public $timestamps  = false;
    protected $table = "country";

    use HasFactory;

    public function questions() {
        return $this->hasMany(Question::class);
    } 

    public function cities() {
        return $this->hasMany(City::class);
    }
}
