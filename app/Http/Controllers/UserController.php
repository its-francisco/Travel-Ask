<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Post;
use App\Models\Question;
use App\Http\Helper;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    private $accountMapping = [
        'admin' => 'Administrator',
        'moderator' => 'Moderator',
        'verified' => 'Verified',
        'normal' => 'Normal',
    ];


    public function show(string $id) 
    {
        // Get the user.
        $user = User::findOrFail($id);
        if ($user->isDeleted()) return redirect('/')->withErrors('Not a valid user.'); 
        $answers = $user->answers()->get();
        foreach ($answers as $answer) {
            $answer->post = Post::find($answer->post_id);
            $answer->question = Question::find($answer->question_id);
        }
        $questions = $user->questions()->get();
        $questions->map(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });
        // Use the pages.user template to display the user.
        return view('pages.user', [
            'user' => $user,
            'questions' => $questions,
            'comments' => $user->comments()->get(),
            'answers'=> $answers->filter(function($answer) { return !$answer->deleted; })
        ]);
    }

    public function edit(Request $request, int $id)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'bio' => 'nullable|string|max:1000',
            'site' => 'nullable|url',
            'travelling' => 'nullable|string',
            'notifications' => 'nullable|string', 
        ]);

        $user = User::findOrFail($id);
        

        // check if user is authorized here!
        $this->authorize('edit', $user);

        $user->name = $request->name;
        $user->bio = $request->bio;
        $user->site = $request->site;
        $user->travelling = $request->has('travelling') ? true : false;
        $user->notifications =  $request->has('notifications') ? true : false;

        $user->save();

        return redirect()->route('user.show', ['id' => $user->id])->withSuccess('You have successfully edited your profile!');

    }

    public function editpf(Request $request, int $id) {
        $request->validate([
            'photo' => 'required|mimes:jpg,bmp,png',
        ]);

        $user = User::findOrFail($id);
        

        // check if user is authorized here!
        $this->authorize('edit', $user);

        $file = $request->file('photo');
        $fileName = $file->hashName();
        $file->storeAs('profile', $fileName, FileController::$diskName);
        $user->photo = $fileName;

        $user->save();
        
        return response()->json($fileName);
    }

    public function add(Request $request)
    {
        $this->authorize('accessAdmin', User::class);
        $validatedData = $request->validate([
            'name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:50|unique:users',
            'account' => 'required|string|in:admin,moderator,verified,normal',
        ]);

        // convert format received to the one db understands
        $validatedData['account'] = $this->accountMapping[$validatedData['account']];
        $password = Str::random(14);
        $validatedData['password'] = bcrypt($password);

        User::create($validatedData);

        return redirect()->back()->with('success', 'User created successfully.')->with('password', $password);
    }

    public function delete(Request $request, int $id)
    {
        $user = User::findOrFail($id);

        $this->authorize('delete', $user);
        if (Auth::id() === $id) {
            Auth::logout();
        }
        $user->delete();
        session()->flash('success', 'User deleted successfully.');

        return response()->json($user);
    }

    public function search(Request $request) {
        $request->validate([
            'limit' => 'integer|min:1|max:100',
            'page' => 'integer|min:1',
        ]);
        $request->mergeIfMissing(['query' => '', 'limit' => 10, 'page' => 1]);
        $query = $request->query('query');
        $limit = $request->query('limit');
        $offset = intval($limit) * ($request->integer('page') - 1);
        $result = User::select('id', 'username', 'name', 'email')
            ->where('username', 'LIKE', "%{$query}%")
            ->where('email', 'LIKE', "%@%");
        $count = $result->count();
        $users = $result->offset($offset)
            ->limit($limit)
            ->get();
        foreach ($users as &$user) {
            $user['questions'] = $user->questions()->count();
            $user['answers'] = $user->answers()->count();
        }
        return response()->json(['users' => $users, 'count' => $count]);
    }
    public function block(Request $request, $id) {
        $user = User::findOrFail($id);

        $this->authorize('block', $user);

        $user->blocked = True;
        $user->save();
        return response()->json($user);
    }
    public function unblock(Request $request, $id) {
        $user = User::findOrFail($id);

        $this->authorize('block', $user);

        $user->blocked = False;
        $user->save();
        return response()->json($user);
    }
}
