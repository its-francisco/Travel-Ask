<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Http\Helper;
use App\Models\Tag;

class SearchController extends Controller
{
    /**
     * Handle the search request.
     */
    public function search(Request $request)
    {
        $request->validate([
            'limit' => 'integer|min:1',
            'query' => 'nullable|string',
            'sort' => 'nullable|string|in:relevance,newest,oldest,views,votes',
            'filter' => 'nullable|string|in:noAnswer,answer,noCorrectAnswer,correctAnswer',
        ]);
        // set default values
        $limit = $request->input('limit', 5);
        $sort = $request->input('sort', ($request->has('query') ? 'relevance' : 'newest'));
        $filter = $request->input('filter');

        // initialize query builder
        $result = Question::query();

        // if query is present in the get method
        if ($request->has('query')) {
            // extract info from query
            $query = $request->query(key: 'query');
            if (!empty($query)) {
                preg_match_all('/\[(.*?)\]/', $query, $matches);
                $tags = $matches[1];
                preg_match_all('/\{country:\s*(.*?)\}/', $query, $matches);
                $country = $matches[1];
                preg_match_all('/\{city:\s*(.*?)\}/', $query, $matches);
                $city = $matches[1];
                
                // clean text
                $cleanedText = preg_replace('/\[.*?\]/', '', $query);
                $cleanedText = preg_replace('/\{[^}]*\}/', '', $cleanedText);
                if (!empty(trim($cleanedText))) {
                    $result->select([
                        'question.post_id',
                        'title',
                        'country_id',
                        'city_id',
                        'view_count',
                    ])
                    ->whereRaw("tsvectors @@ plainto_tsquery('english', ?)", [$cleanedText]);
                }
                else {
                    $result->select(['post_id', 'title', 'country_id', 'city_id', 'view_count']);
                }
                // filter by tags
                if (!empty($tags)) {
                    $tags = array_map(function ($tag) use (&$result) {
                        $tag = Tag::where('name', strtolower($tag))->first();
                        if ($tag) {
                                $result->whereHas('tags', function ($tagQuery) use ($tag) {
                                $tagQuery->where('tag.id', $tag->id);
                            });
                        }
                        else $result->whereRaw('1 = 0'); 
                        return $tag;
                    }, $tags);
                    $tags = array_filter($tags);
                } 
                // filter by country
                if (!empty($country)) {
                    $country = ucwords(strtolower($country[0]));
                    $result->whereHas('country', function ($countryQuery) use ($country) {
                        $countryQuery->where('country.name', '=',$country);
                    });
                }
                // filter by city
                if (!empty($city)) {
                    $city = ucwords(strtolower($city[0]));
                    $result->whereHas('city', function ($cityQuery) use ($city) {
                        $cityQuery->where('city.name', '=',$city);
                    });
                }
            } else {
                $result->select(['post_id', 'title', 'country_id', 'city_id', 'view_count']);
            }
        }
       // apply filters
        switch ($filter) {
            case 'noAnswer':
                $result->doesntHave('answers');
                break;
            case 'answer':
                $result->has('answers');
                break;
            case 'noCorrectAnswer':
                $result->whereDoesntHave('answers', function ($query) {
                    $query->where('correct', true);
                });
                break;
            case 'correctAnswer':
                $result->whereHas('answers', function ($query) {
                    $query->where('correct', true);
                });
                break;
        }
        // apply sorting
        switch ($sort) {
            case 'relevance':
                if ($request->has('query') && !empty($query)) $result->orderByRaw("ts_rank(tsvectors, plainto_tsquery('english', ?)) DESC", [$cleanedText]);
                else $result->orderBy('question.view_count', 'DESC');
                break;
            case 'newest':
                $result->join('post', 'post.id', '=', 'question.post_id')
                       ->orderBy('post.date', 'DESC');
                break;
            case 'oldest':
                $result->join('post', 'post.id', '=', 'question.post_id')
                       ->orderBy('post.date', 'ASC');
                break;
            case 'votes':
                $result->join('post', 'post.id', '=', 'question.post_id')
                       ->orderBy('post.upvotes', 'DESC');
                break;
            case 'views':
                $result->orderBy('question.view_count', 'DESC');
                break;
        }
        // paginate the results
        $questions = $result->paginate($limit)->onEachSide(1);
        $questions->getCollection()->transform(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });

        $params = [];
        if ($request->has('query')) $params['query'] = $request->query('query');

        if ($request->has('limit')) $params['limit'] = $request->query('limit');

        if ($request->has('sort')) $params['sort'] = $request->query('sort');

        if ($request->has('filter')) $params['filter'] = $request->query('filter');

        // return the view with search results
        $questions->appends($params);
        return view('pages.results', [
            'questions' => $questions,
            'tags' => $tags ?? '',
            'country' => $country ?? '',
            'city' => $city ?? '',
            'queryText' => $cleanedText ?? '',
        ]);
    }
}
