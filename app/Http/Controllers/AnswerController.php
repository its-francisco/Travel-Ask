<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Post;
use App\Models\User;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Helper;
use App\Models\Vote;
use App\Models\FollowQuestion;

use Illuminate\Support\Facades\Log;

class AnswerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request, $questionId)
    {
        $this->authorize('create', Answer::class);

        $request->validate([
            'content' => ['required', 'string', function($attribute, $value, $fail) {
                $textContent = Helper::plainContent($value);
                if (strlen($textContent) > 10000) {
                    $fail("The content exceeds the maximum length of 10000 characters.");
                }
                if (strlen($textContent) === 0) {
                    $fail("The answer content must include text.");
                }
            }],
        ]);

        Question::findOrFail($questionId);

        $id = DB::transaction(function () use ($request, $questionId) {
            DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $post = Post::create([
                'content' => $request['content'],
                'user_id' => Auth::user()->id,
            ]);
    
            $answer = Answer::create([
                'post_id' => $post->id,
                'question_id' => $questionId,
            ]);
            return $post->id;
        }, 2);

        $answer = Answer::find($id);
        //needed so it doesnt lazy load, that is, it forces to load the post with the answer even if it might not be used
        
        // send notification

        NotificationController::sendAnswerNotification($questionId);
        
        $existingFollow = FollowQuestion::where('user_id', Auth::user()->id)
                                ->where('question_id', $questionId)
                                ->first();
        if (!$existingFollow) {

            FollowQuestion::create([
                'user_id' => Auth::user()->id,
                'question_id' => $questionId,
            ]);
        }

        $answer->load('post');
        $answer->post->load('user');
        if ($request->wantsJson()) return response()->json(['answer' => $answer]);
        else return redirect()->route('question.show', ['id' => $answer->question_id])->withSuccess('Answer created successfully');

    }

    /**
     * Display the specified resource.
     */
    public function show(Answer $answer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Answer $answer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'content' => ['required', 'string', function($attribute, $value, $fail) {
                $textContent = Helper::plainContent($value);
                if (strlen($textContent) > 10000) {
                    $fail("The content exceeds the maximum length of 10000 characters.");
                }
                if (strlen($textContent) === 0) {
                    $fail("The answer content must include text.");
                }
            }],
        ]);

        $answer = Answer::findOrFail($id);
        $this->authorize('update', $answer);
        $answer->post->content = $request['content'];
        $answer->post->save();
        return response()->json(['post' => $answer->post]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(Request $request, int $id)
    {
        if ($request->force) {
            return $this->forceDelete($id);
        }
        $answer = Answer::findOrFail($id);
        $this->authorize('delete', $answer);
        $answer->delete();
        return response()->json(['id' => $answer->post_id]);
    }

    public function forceDelete(int $id)
    {
        $answer = Answer::findOrFail($id);
        $this->authorize('forceDelete', $answer);
        $answer->comments()->delete();
        $answer->delete();
        return response()->json(['id' => $answer->post_id]);
    }

    public function markcorrect(Request $request, int $id){
        $answer = Answer::findOrFail($id);
        $this->authorize('update', $answer);

        // leave if we only want one answer by question
        //if($answer->question->hasCorrectAnswer()){
        //    return redirect()->route('question.show', ['id' => $answer->question_id])->withErrors('There is already a correct answer');
        //}
        $answer->markAsCorrect();
        return redirect()->route('question.show', ['id' => $answer->question_id])->withSuccess('Success registering correct answer');
    }

    public function getVotes($id)
    {
        $answer = Answer::findOrFail($id);
        $votes = Vote::where('post_id', $answer->post_id)->get();
        return response()->json($votes);
    }

    public static function hasUserVoted($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->json(['hasVoted' => false]);
        }
        $answer = Answer::findOrFail($id);
        $vote = Vote::where('post_id', $answer->post_id)->where('user_id', $user->id)->first();
        return response()->json(['hasVoted' => $vote ? true : false, 'vote' => $vote ? $vote->vote : null]);                                  
    }
}
