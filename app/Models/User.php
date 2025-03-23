<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\FileController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// Added to define Eloquent relationships.
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;
    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'username',
        'name',
        'email',
        'password',
        'photo',
        'account' // be extra careful: DON'T send request to User::create without protection. User may change his role... 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
       //'password' => 'hashed', -> we already do this verification elsewhere. This is useful to be disabled to Google accounts
       'travelling' => 'boolean',
       'notifications' => 'boolean',
    ];


    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function posts() {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function answers(){
        return $this->hasManyThrough(Answer::class, Post::class, 'user_id', 'post_id', 'id', 'id');
    }

    public function questions(){
        return $this->hasManyThrough(Question::class, Post::class, 'user_id', 'post_id', 'id', 'id');
    }

    // these functions allow separation of domains
    // this way, details about how info is stored are hidden from the rest of the app
    
    public function isAdmin(){
        return $this->account === "Administrator";
    }

    public function isModerator(){
        return $this->account === "Moderator";
    }

    public function isVerified(){
        return $this->account === "Verified";
    }
    public function isNormal(){
        return $this->account === "Normal";
    }
    public function isBlocked(){
        return $this->blocked === True;
    }
    public function isDeleted(){
        return $this->deleted === True;
    }
    public function isTavelling(){
        return $this->travelling === True;
    }

    public function getProfileImage(){
        return FileController::get('profile', $this->id);
    }
    public static function getDefaultImage(){
        return FileController::get('profile', -1);
    }
    public function followedTags()
    {
        return $this->belongsToMany(Tag::class, 'follow_tag', 'user_id', 'tag_id');
    }
}
