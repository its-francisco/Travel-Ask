<?php
 
namespace App\Http\Controllers;

use App\Models\Question;
use App\Http\Helper;

use Illuminate\View\View;
use App\Events\Notification;


class FeedController extends Controller
{

    // This functions gets the questions that the user is following and the questions that have tags that the user is following.
    // So that the feed is adjusted to the user's preferences.
    public function index()
    {
        $followedQuestions = Question::join('post', 'question.post_id', '=', 'post.id')
                            ->join('follow_question', 'question.post_id', '=', 'follow_question.question_id')
                            ->where('follow_question.user_id', '=', auth()->user()->id)
                            ->select('question.*')
                            ->get();

        $followedQuestionsTags = Question::whereHas('tags', function ($query)  {
            $query->whereIn('id', auth()->user()->followedTags->pluck('id')->toArray());
        })->get();
        
        $allFollowedQuestions = $followedQuestions->merge($followedQuestionsTags)->unique('post_id');
        $allFollowedQuestions = $allFollowedQuestions->map(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });
        // Pass the questions to the home view
        return view('pages.feed')->with('feed', $allFollowedQuestions);
    }

}