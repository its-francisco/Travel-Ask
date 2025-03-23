<?php
 
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

use Illuminate\View\View;
use App\Events\Notification;


class LoginController extends Controller
{

    /**
     * Display a login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect('/');
        } else {
            return view('auth.login');
        }
    }

    /**
     * Handle an authentication attempt.
     */
    public function authenticate(Request $request): RedirectResponse
    {
        if (Auth::check()) {return redirect()->intended('/')->withSuccess('You have logged in successfully!');}
        // faz validação dos dados 
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            if (Auth::user()->isBlocked()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['error' => 'You have been blocked.']);
            }
            if ($request->has('overlay')) {
                return redirect()->back()->withSuccess('You have logged in successfully!');
            }
            return redirect()->intended('/')->withSuccess('You have logged in successfully!');
        }
 
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log out the user from application.
     */
    public function logout(Request $request)
    {
        $user_id = Auth::user()->id;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // sends headers to avoid lookup in history of past users
        return redirect()->route('login')
         ->withHeaders([
            'X-Logged-Out' => true,
            'X-User-ID' => $user_id,
        ])
        ->withSuccess('You have logged out successfully!');
    } 
}
