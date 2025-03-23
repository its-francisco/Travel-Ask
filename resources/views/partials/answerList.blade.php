<li class="answer" data-id="{{$answer->post_id}}">
    <a href="{{route('question.show', ['id' => $answer->question->post_id])}}#{{$answer->post_id}}-answer-content">
        <article class="answer">
                <h4 class="question-title">{{ $answer->question->title }}</h4>
                <div class="answer-text">{!! $answer->post->content !!}</div>
            <div class="votes">
                <span class="material-symbols-outlined" title="UpVoteCount">thumb_up</span>
                <span>{{$answer->post->upvotesCount()}}</span>
                <span class="material-symbols-outlined" title="DownVoteCount">thumb_down</span>
                <span>{{$answer->post->downvotesCount()}}</span>
            </div>
        </article>
    </a>
</li>