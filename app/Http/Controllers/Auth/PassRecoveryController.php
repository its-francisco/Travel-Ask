<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Mail\MailModel;
use Illuminate\Support\Facades\Mail;
use App\Models\PasswordReset;
use Illuminate\Support\Facades\Hash;




class PassRecoveryController extends Controller
{
    public function showRecoverPage(){
        if (Auth::check()) {
            return redirect('/');
        } else {
            return view('auth.recovery');
        }
    }


    public function handleRecover(Request $request){
         // Validate the email input
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();


        if(!$user){
            // this way, we do not give any idea if the email exists!
            return redirect()->route('login')->withSuccess('Recovery email sent successfully');
        }


        $token = Str::random(30);
        $link = url('/recovery', $token);

        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );


        $mailData = [
            'type' => 'passrecovery',
            'name' => $user->name,
            'link' => $link,
            'subject' => 'Password Recovery'
        ];

        // send the email
        Mail::to($user->email)->send(new MailModel($mailData));

        return redirect()->route('login')->withSuccess('Recovery email sent successfully');
    }

    private function tokenExpired($createdAt){
        $createdAt = new \DateTime($createdAt);
        $now = new \DateTime();
        $interval = $now->diff($createdAt);
        return $interval->h >= 1; 
    }

    
    public function changePasswordPage($token){
        $passwordReset = PasswordReset::where('token', $token)->first();

        if(!$passwordReset){
            return redirect()->route('login')->withErrors('Invalid token.');
        }
        if($this->tokenExpired($passwordReset->created_at)){
            return redirect()->route('login')->withErrors('Expired token.');
        }

        
        
        return view('auth.reset');

    }


    public function changePassword($token, Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:10|confirmed'
        ]);

        $passwordReset = PasswordReset::where('token', $token)->where('email', $request->email)->first();

        if(!$passwordReset){
            return redirect()->route('login')->withErrors('Invalid token.');
        }
        if($this->tokenExpired($passwordReset->created_at)){
            return redirect()->route('login')->withErrors('Expired token.');
        }
        // after this line, the email is associated with the token and is valid
        

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        PasswordReset::where('email', $request->email)->delete();
        return redirect()->route('login')->withSuccess('Password updated successfully.');

    }

}
