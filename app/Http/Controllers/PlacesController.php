<?php

namespace App\Http\Controllers;


use App\Models\Country;
use App\Models\City;
use App\Http\Helper;

use Illuminate\Http\Request;

class PlacesController extends Controller
{
    // Show each country page
    public function showCountry(string $id)
    {
        $country = Country::findOrFail($id);
        $questions = $country->questions->map(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });
        return view('pages.place', ['name' => $country->name, 'description' => $country->description, 'city'=> false, 'questions' => $questions]);
    }



    public function showCity(string $id)
    {
        $city = City::findOrFail($id);
        $questions = $city->questions->map(function ($question) {
            $question->post->content = Helper::plainContent($question->post->content);
            return $question;
        });
        return view('pages.place', ['name' => $city->name, 'description' => $city->description, 'city'=> true, 'questions' => $questions, 'id' => $id]);

    }

    // legacy - for api requests!
    public function cities(Int $id)
    {
        $country = Country::findOrFail($id);
        return response()->json($country->cities);
    }


    // this is mainly intended for home page. no security needs.
        public function trendingLocations(){
            $trendingCountries = Country::query()
                ->whereHas('questions')
                ->withCount([
                    'questions as recent_count' => function ($query) {
                        $query->whereHas('post', function ($q) {
                            $q->where('date', '>=', now()->subDays(30));
                        });
                    }
                ])
                ->orderByDesc('recent_count')
                ->limit(10)
                ->get();

            return response()->json($trendingCountries);
        }

    public function events(Request $request, Int $id) {
        $city = City::findOrFail($id);
        $events = $city->events;
        return response()->json($events);
    }
}
