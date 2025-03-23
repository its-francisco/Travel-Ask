<div class="votes" data-post-id="{{ $post->id }}">
    <button class="button-icon material-symbols-outlined thumb-up {{$vote->hasVoted && $vote->vote === 'Up' ? 'upvoted' : ''}}" data-vote="Up" title="UpVote">thumb_up</button>
    <span class="upvotes-count">{{$post->upvotesCount()}}</span>
    <button class="button-icon material-symbols-outlined thumb-down {{$vote->hasVoted && $vote->vote === 'Down' ? 'downvoted' : ''}}" data-vote="Down" title="DownVote">thumb_down</button>
    <span class="downvotes-count">{{$post->downvotesCount()}}</span>
</div>