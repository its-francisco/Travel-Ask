<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;
    protected $table = 'password_resets';
    public $timestamps  = false;
    protected $keyType = 'string';
    protected $primaryKey = 'email';
    public $incrementing = false;


    protected $fillable = [
        'email',
        'token',
        'created_at',
    ];



}
