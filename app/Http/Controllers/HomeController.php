<?php
 
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Tag;
use App\Http\Helper;
use App\Models\Country;

use App\Events\Notification;

use App\Http\Controllers\NotificationController;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{

    // This function gets the questions ordered by view count and penalizes older posts to show the most relevant questions
    // Hot topics
    public function index()
    {  
        // Retrieve questions ordered by view count (70% of weight) and penalizes older posts
        $questions = Question::join('post', 'question.post_id', '=', 'post.id')
                    ->select(
                        'question.*',
                        'post.date',
                        'post.content',
                        DB::raw('(question.view_count * 0.7 + (EXTRACT(EPOCH FROM (NOW() - post.date)) / 86400) * -0.3) AS weight')
                    )
                    ->orderByDesc('weight') // Order by calculated weight
                    ->limit(6)
                    ->get();
        $questions = $questions->map(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });
        $tags = Tag::withCount('questions')  
           ->orderByDesc('questions_count')  
           ->limit(10)
           ->get();  

        return view('pages.home')->with(['hot_topics' => $questions, 'tags' => $tags]);
    }


    public function about(){
        return view('pages.about');
    }

}
