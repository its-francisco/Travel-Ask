@php
    use App\Models\Comment;
    $comments = $post->comments();
    $count = $comments->count();
@endphp

<section class="comment-section" data-id="{{ $post->id }}">
    <div>
        <div class="comments-sum">
            <p>{{$count}}</p>
            <button class="button-icon material-symbols-outlined icon" title="Show comments">mode_comment</button>
        </div>
    </div>
    @if ($count !== 0)
        <ul class="comments hide">
            @each('partials.comment', $comments->get(), 'comment')
        </ul>
    @endif

    <input type="button" class="add-comment hide" value="add a comment here">
    <form class="create_comment hide">
        <label>Write your comment here: <abbr class="requiredField" title="mandatory field">*</abbr>
            <textarea name="content" class="new_comment_content" maxlength="1000" cols="10" rows="10" placeholder="Write your comment here"></textarea>
        </label>
        <button type="submit" data-id="{{$post->id}}">Create</button>
    </form>
</section>