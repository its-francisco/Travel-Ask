<li class="comment" data-id="{{$comment->id}}">
    @auth
        @can('update', $comment)
            <div class="edit-zone">
                <button class="edit-comment button-icon material-symbols-outlined" data-id="{{$comment->id}}" title="Edit Comment">edit</button>
                <div class="confirmation">
                    <button class="confirm-action delete-comment">
                        <span class="material-symbols-outlined" title="Delete Comment">delete</span>
                    </button>
                </div>
                <form data-id="{{$comment->id}}" class="edit-comment-form">
                    <textarea name="content" maxlength="1000">{{$comment->content}}</textarea>
                    <button type="submit" class="submit-edit-comment">Save</button>
                </form>
            </div>
        @endcan
    @endauth
    <p class="comment-content">{{$comment->content}}</p>
    @if($comment->user && !$comment->user->isDeleted())
        <a href="/users/{{ $comment->user_id }}">{{$comment->user->name}}</a>
    @else
        <p class="user">Unknown</p>
    @endif
</li>