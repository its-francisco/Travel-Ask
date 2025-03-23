<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VoteNotification;
use App\Models\AnswerNotification;
use App\Models\Post;
use App\Events\Notification;
use App\Models\User;
use App\Models\Question;
use App\Models\Answer;
use App\Models\FollowQuestion;


class NotificationController extends Controller
{


    public static function isUserSubscribed($user_id){
        $user = User::find($user_id);
        return $user ? $user->notifications : false;
    }

    // id of post and boolean to check if is answer
    public static function getAllFollowersOfContent($content_id, $is_answer){
        $followers = [];
        $question = Question::findOrFail($content_id);
        $followQuestions = FollowQuestion::where('question_id', $content_id)->get();

        foreach ($followQuestions as $follow) {
                if(self::isUserSubscribed($follow->user_id)){
                    array_push($followers, $follow->user_id);
                }
            }

        // add author of question to followers
        if(self::isUserSubscribed($question->user_id)){
            array_push($followers, $question->user_id);
        }
        
        $question_tags = $question->tags()->get();
        // given the info of the tag, follow all people that follow that tag
        foreach ($question_tags as $tag) {
            
            $tag_followers = $tag->followers;
            foreach ($tag_followers as $follower) {
                if ($follower->id !== null && !in_array($follower->id, $followers)) {
                    array_push($followers, $follower->id);
                }
            }
        }

        return $followers;
    }


    //
    public static function sendVoteNotification($question_id){
        
        // get the elements that are in the follow question
        $followers = self::getAllFollowersOfContent($question_id, FALSE);

        // get all the elements in follow tags

        $question = Question::find($question_id);

        // iterate over followers and send them the vote
        foreach ($followers as $user_id) {
            $user = User::find($user_id);
            if ($user && $user->notifications) {
                event(new Notification('New vote on ' . $question->title, $question_id, 'vote', $user_id));
            }
        }
        
    }

    public static function sendAnswerNotification($question_id){
        $followers = self::getAllFollowersOfContent($question_id, FALSE);
        $question_title = Question::findOrFail($question_id)->title;
        foreach($followers as $user_id){
            // here, perharps, we will have to have a loop to send to all the users 
            $user = User::find($user_id);
            if ($user && $user->notifications) {
                event(new Notification('New answer on ' . $question_title, $question_id, 'answer', $user_id));
            }
        }
    }



    public function getVoteNotification(){
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Not authenticated'], 401);;
        }
        $notifications = VoteNotification::where('notified', $user->id)
                                         ->where('viewed', false)
                                         ->get();
        return response()->json($notifications);
    }

    public function getAnswerNotification(){
        $user = Auth::user();
        if(!$user){
            return response()->json(['error' => 'Not authenticated'], 401);;
        }
        $notifications = AnswerNotification::where('notified', $user->id)
                                         ->where('viewed', false)
                                         ->get();
        return response()->json($notifications);
    }

    public function markViewed(Request $request){
        // check if type and id are defined
        $request->validate([
            'type' => 'required|string|in:vote,answer',
            'id' => 'required|integer',
        ]);
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Not authenticated'], 401);
        }
        // get notification
        if ($request->type === 'vote') {
            $notification = VoteNotification::where('id', $request->id)
                                            ->where('notified', $user->id)
                                            ->first();
        } else {
            $notification = AnswerNotification::where('id', $request->id)
                                              ->where('notified', $user->id)
                                              ->first();
        }
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        // mark as viewed
        $notification->update(['viewed' => true]);

        return response()->json(['message' => 'Notification marked as viewed']);
    }

    public function getNotifications(){
        $voteNotifications = VoteNotification::where('notified', auth()->user()->id)->where('viewed', false)
        ->join('users', 'vote_notification.voter', '=', 'users.id')
        ->orderBy('date', 'desc')
        ->get(['vote_notification.*', 'users.username as username']);

        $answerNotifications = AnswerNotification::where('notified', auth()->user()->id)->where('viewed', false)
        ->join('answer', 'answer_notification.answer_id', '=', 'answer.post_id')
        ->join('question', 'answer.question_id', '=', 'question.post_id')
        ->join('post', 'answer.post_id', 'post.id')
        ->join('users', 'post.user_id', 'users.id')
        ->orderBy('date', 'desc')
        ->get(['answer_notification.*', 'answer.question_id as post_id', 'users.username as username', 'question.title as question_title']);

        // combine the notifications
        $notifications = $voteNotifications->merge($answerNotifications);
        $sortedNotifications = $notifications->sortByDesc('date')->values();

        return response()->json($sortedNotifications);
    }
}
