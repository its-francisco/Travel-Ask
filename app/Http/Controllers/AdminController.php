<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function main()
    {
        // the main page of the admin pannel!

        $this->authorize('accessAdmin', User::class);
        $users = User::count();
        
        return view('pages.admin.main', ['users' => $users]);
    }

    public function tagPanel() {
        $this->authorize('accessAdmin', User::class);
        $tags = Tag::all();
        return view('pages.admin.tags', ['tags' => $tags]);
    }
}
