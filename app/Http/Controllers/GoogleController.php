<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class GoogleController extends Controller
{
    public function redirect() {
        return Socialite::driver('google')->redirect();
    }

    public function callbackGoogle() {

        $google_user = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $google_user->getEmail())->first();
        // If the user does not exist, create one
        if (!$user) {

            $avatarUrl = $google_user->getAvatar();
            $avatarContents = file_get_contents($avatarUrl);
            $fileName = 'google_' . Str::random(14) . '.png';

            // Store the avatar in the disk
            Storage::disk('public')->put("profile/". $fileName, $avatarContents);
            // get the first part of the email before the @
            $username = substr(explode('@', $google_user->getEmail())[0], 0, 45) . Str::random(3);


            $new_user = User::create([
                'username' => $username,
                'name' => $google_user->getName(),
                'email' => $google_user->getEmail(),
                'password' => '0',
                'account' => 'Normal',
                'photo' => $fileName,
            ]);

            Auth::login($new_user);

        // Otherwise, simply log in with the existing user
        } else {
            Auth::login($user);
        }

        // After login, redirect to homepage
        return redirect('/');
    }

}
