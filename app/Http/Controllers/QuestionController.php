<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

use App\Models\Question;
use App\Models\Post;
use App\Models\Country;
use App\Models\City;
use App\Models\User;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Models\Vote;
use App\Http\Helper;

use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;


class QuestionController extends Controller
{
    /**
     * Show the form for creating a new question.
     */
    public function showQuestionForm()
    {
        //$this->authorize('create', Question::class); cannot set errors..,
        // this verification of auth seems odd.. try to use policies!
        if (!Auth::check()) {
            // Not logged in, redirect to login.
            return redirect('/login')->withErrors('You must be logged in to ask a question');

        } else {
            $countries = Country::all();
            $tags = Tag::all();
            return view('pages.create_question', [
                'countries' => $countries,
                'tags' => $tags
            ]);
        }
    }

    // This function creates a new question and does the adequate backend validation to esure data integrity

    public function add(Request $request) {
        $request->validate([
            'content' => ['required', 'string', function($attribute, $value, $fail) {
                $textContent = Helper::plainContent($value);
                if (strlen($textContent) > 10000) {
                    $fail("The content exceeds the maximum length of 10000 characters.");
                }
                if (strlen($textContent) === 0) {
                    $fail("The content must include text.");
                }
            }],
            'title' => 'required|string|max:100|unique:question,title',
            'country' => 'nullable|integer|exists:country,id',
            'city' => 'nullable|integer|exists:city,id',
            'tags' => 'array', 
            'tags.*' => 'integer|exists:tag,id',
        ]);

        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['msg' => 'Authentication needed to post.']);
        }
        if ($request->filled('country') && $request->filled('city')){
            $city = City::find($request->integer('city'));
            if ($city->country_id !== $request->integer('country')) {
                return redirect()->back()->withErrors(['msg' => 'Invalid city']);
            }
        }


        $id = DB::transaction(function () use ($request) {
            DB::statement('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');
            $post = Post::create([
                'content' => $request->content,
                'user_id' => Auth::user()->id,
            ]);
            $question_data = [
                'post_id' => $post->id,
                'title' => $request->title,
            ];
            if (!empty($request->country)) {
                $question_data['country_id'] = $request->country;
                if (!empty($request->city)) {
                    $question_data['city_id'] = $request->city;
                }
            }
            $question = Question::create($question_data);
            return $post->id;
        }, 2);   
        
        if (isset($id)) {
            $question = Question::find($id);
            $question->tags()->sync($request->input('tags'));
            PushNotifications::sendPushNotification("New question on Travel&Ask", $request->title);
        }        
        if ($request->wantsJson()) return response()->json($id);
        else return redirect()->route('question.show', ['id' => $id])->withSuccess('Question created successfully');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $question = Question::findOrFail($id);
        if (!Session::has("viewed_question_{$id}")) {
            $question->increment('view_count');

            // Mark the question as viewed in this session
            Session::put("viewed_question_{$id}", true);
        }

        $countries = Country::all();
        $tags = Tag::all();
        // Use the pages.card template to display the card.
        return view('pages.question', [
            'question' => $question,
            'countries' => $countries,
            'tags' => $tags,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
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
            'title' => ['required', 'string', 'max:100',  function($attribute, $value, $fail) use($id) {
                $exists = Question::where('title', $value)->whereNot('post_id', $id)->exists();
                if ($exists) {
                    $fail("The {$attribute} has already been taken.");
                }
            }],
            'country' => 'nullable|integer|exists:country,id',
            'city' => 'nullable|integer|exists:city,id',
            'tags' => 'array', 
            'tags.*' => 'integer|exists:tag,id',
        ]);
        $city = City::find($request->integer('city'));

        if ($request->filled('city') && ($city->country_id !== $request->integer('country'))) {
            return response()->json(['message' => 'Invalid City'], 422);
        }
        $question = Question::findOrFail($id);
        $this->authorize('update', $question);
        $question->post->content = $request['content'];
        $question->title = $request['title'];
        $question->country_id = $request['country'];
        $question->city_id = $request['city'];
        $question->post->save();
        $question->save();
        $newTags = $request->input('tags');  
        $question->tags()->sync($newTags);
        return response()->json([
            'content' => $question->post->content,
            'title' => $question->title
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete(int $id)
    {
        $question = Question::findOrFail($id);
        $this->authorize('delete', $question);
        $answers = $question->answers;
        foreach ($answers as $answer) {
            $answer->forceDelete();
        }
        $question->comments()->delete();
        $question->delete();
        session()->flash('success', 'Question deleted successfully.');
        return response()->json();
    }

    // This function searches for questions based on the query and returns the results
    // Querys are strings that can have normal text or tags, countries, cities, accordingly to the help pop-up

    public function search(Request $request) {
        $request->validate([
            'query' => 'string',
        ]);
        $result = Question::query();
        $query = $request->query('query');
        if(!empty($query)) {
            $result->select([
                    'post_id',
                    'title',
                    'country_id',
                    'city_id',
                    'view_count',
                ])
                ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$query])
                ->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$query]);
        }
        else {
            $result->select(['post_id', 'title', 'country_id', 'city_id', 'view_count']);
        }
        $count = $result->count();
        
        $questions = $result->limit(10)->get();
        $questions->map(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });
        return response()->json(['questions' => $questions, 'count' => $count]);
    }

    public function deleteAuthor(int $id) {
        $question = Question::findOrFail($id);
        $this->authorize('removeAuthor', $question);

        $question->post->user_id = null;
        $question->post->save();
        $question->save();
        session()->flash('success', 'Authorship removed successfully.');
        return response()->json();
    }

    public function getVotes($id)
    {
        $question = Question::findOrFail($id);
        $votes = Vote::where('post_id', $question->post_id)->get();
        return response()->json($votes);
    }

    // This functions checks if the auth user has voted on a question and returns the vote value so that when
    // the user enters the question page, the vote button is adjusted
    public static function hasUserVoted($id)
    {
        $user = Auth::user();
        if ($user == null) {
            return response()->json(['hasVoted' => false]);
        }
        $question = Question::findOrFail($id);
        $vote = Vote::where('post_id', $question->post_id)->where('user_id', $user->id)->first();
        return response()->json(['hasVoted' => $vote ? true : false, 'vote' => $vote ? $vote->vote : null]);
    }
}
