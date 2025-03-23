<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FollowQuestion;
use App\Models\Question;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Auth\Access\AuthorizationException;

class FollowQuestionController extends Controller
{

    // This function toggles the follow of a question
    public function toggleFollow($id)
    {
        $question = Question::findOrFail($id);
        $this->authorize('create', FollowQuestion::class);
        $user = Auth::user();

        $follow = FollowQuestion::where('question_id', $question->post_id)
                                ->where('user_id', $user->id)
                                ->first();

        if ($follow != null) {
            // If user follows the question, unfollow
            $deleted = FollowQuestion::where('question_id', $question->post_id)->where('user_id', $user->id)->delete();
            return response()->json(['message' => 'Unfollowed successfully']);
        } else {
            // If the user does not follow the question, follow
            $newFollow = new FollowQuestion();
            $newFollow->question_id = $question->post_id;
            $newFollow->user_id = $user->id;
            $newFollow->save();
            return response()->json(['message' => 'Followed successfully']);
        }
    }

    // This function checks if the user is following a question so taht when entering the question page, the follow button is adjusted
    public function isFollowing($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->json(['isFollowing' => false]);
        }
        $question = Question::findOrFail($id);
        $follow = FollowQuestion::where('question_id', $question->post_id)->where('user_id', $user->id)->first();
        return response()->json(['isFollowing' => $follow ? true : false]);
    }
}