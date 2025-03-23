<li class="question-result" data-id="{{$question->post_id}}">
        <article >
        <a href="/questions/{{ $question->post_id }}">
            <div class="questionlist">
                <div class="content-questionlist">
                    <h4 class="title">{{$question->title}}</h4>
                    <p class="content">{{Str::limit($question->post->content, 90)}}</p>
                </div>
                <div>
                    @if ($question->country)
                    <div class="location">
                        <span class="material-symbols-outlined location-icon" title="Location"> location_on </span>
                        <p class="country"> {{$question->country->name}} </p>
                            @if ($question->city)
                            <p class="city"> {{$question->city->name}} </p>
                            @endif
                    </div>
                    @endif
                    <div class="votes">
                        <span class="material-symbols-outlined" title="UpVotesCount">thumb_up</span>
                        <span>{{$question->post->upvotesCount()}}</span>
                        <span class="material-symbols-outlined" title="DownVotesCount">thumb_down</span>
                        <span>{{$question->post->downvotesCount()}}</span>
                    </div>
                </div>
            </div>
        </a>
            <div class="extra-questionList">
                <p>{{ $question->answers->count() }} {{ $question->answers->count() == 1 ? 'answer' : 'answers' }}</p>
                @if($question->post->user && !$question->post->user->isDeleted())
                    <a href="/users/{{ $question->post->user_id }}" class="user">{{$question->post->user->name}}</a>
                @else
                    <p class="user">Unknown</p>
                @endif
            </div>
        </article>
   
</li>