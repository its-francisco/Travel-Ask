@php
    use App\Models\Question;
    use App\Models\Answer;
    $post = $comment->post;
    $question = Question::whereBelongsTo($post)->first() ?? Answer::whereBelongsTo($post)->first()->question;
@endphp

<li class="comment">
    <a href="{{route('question.show', ['id' => $question->post_id])}}">
        <article class="comment">
            <h4>{{ $question->title }}</h4>
            <div class="comment-text">{{$comment->content }}</div>
        </article>
    </a>
</li>


