<li class="answer" id="{{$answer->post_id}}">
    @auth
        <div class="edit-zone">
            @if (!$answer->deleted)
                @can('update', $answer)
                <button class="button-icon material-symbols-outlined edit-answer" id="{{$answer->post_id}}-edit-answer" title="Edit Answer">edit</button>
                @can('forceDelete', $answer)
                    <div class="confirmation"><button class="confirm-action delete-answer force-delete"> <span class="material-symbols-outlined" title="Delete Answer">delete</span></button></div>
                @elsecan('delete', $answer)
                    <div class="confirmation"><button class="confirm-action delete-answer"> <span class="material-symbols-outlined" title="Delete Answer">delete</span></button></div>
                @endcan
                <form id="{{$answer->post_id}}_edit_answer_form" class="hide">
                    <label for="edit-content-{{$answer->post_id}}">Edit your answer: <abbr class="requiredField" title="mandatory field">*</abbr>
                        <textarea id="content-editor-{{$answer->post_id}}" name="content">{{ strip_tags($answer->post->content) }}</textarea></label>
                        <div class="answer-editor" id="editor-{{$answer->post_id}}">{!! $answer->post->content !!}</div>
                    <button type="submit" class="submit-edit-answer">Save</button>
                </form>
                @endcan
            @else
                @can('forceDelete', $answer)
                    <div class="confirmation"><button class="confirm-action delete-answer force-delete"> <span class="material-symbols-outlined" title="Delete Answer">delete</span></button></div>
                @endcan
            @endif
        </div>
    @endauth
    <div id="{{$answer->post_id}}-answer-content" class="answer-content">
        @if (!$answer->deleted)
        <div class="answer-text">{!! $answer->post->content !!}</div>
            @if($answer->post->user && !$answer->post->user->isDeleted())
            <a href="/users/{{ $answer->post->user_id }}" class="author">
                <div class="image-container">
                    <img class="profile_photo {{$answer->post->user->travelling ? "travelling" : ''}}" src="{{ $answer->post->user->getProfileImage() }}"
                         alt="{{$answer->post->user->username}}'s profile photo">
                    @if ($answer->post->user->isVerified())
                        <span class="material-symbols-outlined">verified</span>
                    @endif
                </div>
                <p class="author">{{ $answer->post->user->name}}</p>
            </a>
            @else
                    <p class="author">Unknown</p>
            @endif
            @if ($answer->correct)
                <div class="correct-answer correct"><span class="material-symbols-outlined">check</span></div>
            @else
                @auth
                @can('markCorrect', $answer)
                    <form action="{{ route('answers.markAsCorrect', $answer->post_id) }}" method="POST" class="correct-answer">
                        @csrf
                        <button type="submit" class="material-symbols-outlined" title="Mark answer as Correct">check</button>
                    </form>
                @endcan
                @endauth
            @endif
            @include('partials.votes', ['post' => $answer->post, 'vote' => json_decode(\App\Http\Controllers\AnswerController::hasUserVoted($answer->post_id)->content())])
            <p class="date">{{\Carbon\Carbon::parse($answer->post->date)->diffForHumans()}} </p>
            @if ($answer->post->edit)
                <p class="edited">[Edited]</p>
            @endif
        @else
            <p id="{{$answer->post_id}}-answer-content" class="answer-content">This answer was deleted</p>
        @endif
    </div>
    @include('partials.commentSection', ['post' => $answer->post])
</li>