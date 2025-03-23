@extends('layouts.app')

@section('title', $user->name)

@section('content')


    <!-- Edit Profile -->

    <div id="profile-header">
        @can('edit', $user)
            <script   src="{{ url('js/contentActions.min.js') }}" defer></script>
            <script   src="{{ url('js/user_options.min.js') }}" defer></script>
                <div id="profile-options">
                    <span class="material-symbols-outlined" id="edit" title="Edit Profile">edit</span>
                    <div class=confirmation>
                        <button title="Remove user" class="confirm-action" id="remove-user">
                            <span class="material-symbols-outlined" title="Delete Account"> delete </span>
                        </button>
                    </div>
                @can('block', $user)
                    @if ($user->isBlocked())
                        <div class=confirmation> <button title="Unblock user" class="confirm-action" id="block-user"><span class="material-symbols-outlined">person_check</span></button> </div>
                    @else
                        <div class=confirmation><button title="Block user" class="confirm-action" id="block-user"><span class="material-symbols-outlined"> block</span></button></div>
                    @endif
                @endcan
                </div>
        @endcan

    </div>
    <section id="profile-content">
        <header>
            @auth
            @can('edit', $user)
                <div class="profile-pic">
                    <label for="file">
                    <span class="material-symbols-outlined">photo_camera</span>
                    <span>Change Image</span>
                    </label>
                    <input id="file" type="file" onchange="loadFile(event)"/>
                    <img id="profile-photo" src="{{ $user->getProfileImage() }}" alt="{{$user->username}}'s profile photo">
                </div>
            @else
                <img id="profile-photo" src="{{ $user->getProfileImage() }}" alt="{{$user->username}}'s profile photo">
            @endcan
            @endauth
            @guest
            <img id="profile-photo" src="{{ $user->getProfileImage() }}" alt="{{$user->username}}'s profile photo">
            @endguest
            <div>
                <h2>{{ $user->name }}</h2>
                @if ($user->travelling)
                    <p class="travelling">Travelling</p>
                @endif
                <p>#{{ $user->username }}</p>
                @if ($user->isVerified())
                    <p>Verified</p>
                @endif
            </div>
                <p>{{ $user->bio }}</p>
        </header>
        @can('edit', $user)
        <div class="hide container" id="form-edit-profile">
            <form method="post" action="{{ route('users.edit', ['id' => $user->id])   }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text"  id="name" placeholder="Enter your name here..." name="name" value="{{ $user->name }}">
                    @include('partials.inputError', ['field' => 'name'])
                </div>
                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" placeholder="Enter your bio here..." name="bio">{{ $user->bio }}</textarea>
                    @include('partials.inputError', ['field' => 'bio'])
                </div>
                <div class="form-group">
                    <label for="site">Website</label>
                    <input type="url" id="site" placeholder="Enter your website here..." name="site" value="{{ $user->site }}">
                    @include('partials.inputError', ['field' => 'url'])
                </div>                

                <div class="form-group check">
                    <p>Subscribe to in-app notifications</p>
                    <label class="plane-switch">
                        <input id="notifications" name="notifications" type="checkbox" {{ $user->notifications ? 'checked' : '' }}>
                        <div>
                            <div>
                                <svg viewBox="0 0 13 13">
                                    <path d="M1.55989957,5.41666667 L5.51582215,5.41666667 L4.47015462,0.108333333 L4.47015462,0.108333333 C4.47015462,0.0634601974 4.49708054,0.0249592654 4.5354546,0.00851337035 L4.57707145,0 L5.36229752,0 C5.43359776,0 5.50087375,0.028779451 5.55026392,0.0782711996 L5.59317877,0.134368264 L7.13659662,2.81558333 L8.29565964,2.81666667 C8.53185377,2.81666667 8.72332694,3.01067661 8.72332694,3.25 C8.72332694,3.48932339 8.53185377,3.68333333 8.29565964,3.68333333 L7.63589819,3.68225 L8.63450135,5.41666667 L11.9308317,5.41666667 C12.5213171,5.41666667 13,5.90169152 13,6.5 C13,7.09830848 12.5213171,7.58333333 11.9308317,7.58333333 L8.63450135,7.58333333 L7.63589819,9.31666667 L8.29565964,9.31666667 C8.53185377,9.31666667 8.72332694,9.51067661 8.72332694,9.75 C8.72332694,9.98932339 8.53185377,10.1833333 8.29565964,10.1833333 L7.13659662,10.1833333 L5.59317877,12.8656317 C5.55725264,12.9280353 5.49882018,12.9724157 5.43174295,12.9907056 L5.36229752,13 L4.57707145,13 L4.55610333,12.9978962 C4.51267695,12.9890959 4.48069792,12.9547924 4.47230803,12.9134397 L4.47223088,12.8704208 L5.51582215,7.58333333 L1.55989957,7.58333333 L0.891288881,8.55114605 C0.853775374,8.60544678 0.798421006,8.64327676 0.73629202,8.65879796 L0.672314689,8.66666667 L0.106844414,8.66666667 L0.0715243949,8.66058466 L0.0715243949,8.66058466 C0.0297243066,8.6457608 0.00275502199,8.60729104 0,8.5651586 L0.00593007386,8.52254537 L0.580855011,6.85813984 C0.64492547,6.67265611 0.6577034,6.47392717 0.619193545,6.28316421 L0.580694768,6.14191703 L0.00601851064,4.48064746 C0.00203480725,4.4691314 0,4.45701613 0,4.44481314 C0,4.39994001 0.0269259152,4.36143908 0.0652999725,4.34499318 L0.106916826,4.33647981 L0.672546853,4.33647981 C0.737865848,4.33647981 0.80011301,4.36066329 0.848265401,4.40322477 L0.89131128,4.45169723 L1.55989957,5.41666667 Z" fill="currentColor"></path>
                                </svg>
                            </div>
                            <span class="street-middle"></span>
                            <span class="cloud"></span>
                            <span class="cloud two"></span>
                        </div>
                    </label>
                </div>

                <div class="form-group check">
                    @if ($user->travelling)
                        <p >Are you still travelling?</p>
                    @else
                        <p >Are you travelling?</p>
                    @endif
                    <label class="plane-switch">
                        <input type="checkbox" id="travelling" name="travelling" value="true" {{ $user->travelling ? 'checked' : '' }}>
                        <div>
                            <div>
                                <svg viewBox="0 0 13 13">
                                    <path d="M1.55989957,5.41666667 L5.51582215,5.41666667 L4.47015462,0.108333333 L4.47015462,0.108333333 C4.47015462,0.0634601974 4.49708054,0.0249592654 4.5354546,0.00851337035 L4.57707145,0 L5.36229752,0 C5.43359776,0 5.50087375,0.028779451 5.55026392,0.0782711996 L5.59317877,0.134368264 L7.13659662,2.81558333 L8.29565964,2.81666667 C8.53185377,2.81666667 8.72332694,3.01067661 8.72332694,3.25 C8.72332694,3.48932339 8.53185377,3.68333333 8.29565964,3.68333333 L7.63589819,3.68225 L8.63450135,5.41666667 L11.9308317,5.41666667 C12.5213171,5.41666667 13,5.90169152 13,6.5 C13,7.09830848 12.5213171,7.58333333 11.9308317,7.58333333 L8.63450135,7.58333333 L7.63589819,9.31666667 L8.29565964,9.31666667 C8.53185377,9.31666667 8.72332694,9.51067661 8.72332694,9.75 C8.72332694,9.98932339 8.53185377,10.1833333 8.29565964,10.1833333 L7.13659662,10.1833333 L5.59317877,12.8656317 C5.55725264,12.9280353 5.49882018,12.9724157 5.43174295,12.9907056 L5.36229752,13 L4.57707145,13 L4.55610333,12.9978962 C4.51267695,12.9890959 4.48069792,12.9547924 4.47230803,12.9134397 L4.47223088,12.8704208 L5.51582215,7.58333333 L1.55989957,7.58333333 L0.891288881,8.55114605 C0.853775374,8.60544678 0.798421006,8.64327676 0.73629202,8.65879796 L0.672314689,8.66666667 L0.106844414,8.66666667 L0.0715243949,8.66058466 L0.0715243949,8.66058466 C0.0297243066,8.6457608 0.00275502199,8.60729104 0,8.5651586 L0.00593007386,8.52254537 L0.580855011,6.85813984 C0.64492547,6.67265611 0.6577034,6.47392717 0.619193545,6.28316421 L0.580694768,6.14191703 L0.00601851064,4.48064746 C0.00203480725,4.4691314 0,4.45701613 0,4.44481314 C0,4.39994001 0.0269259152,4.36143908 0.0652999725,4.34499318 L0.106916826,4.33647981 L0.672546853,4.33647981 C0.737865848,4.33647981 0.80011301,4.36066329 0.848265401,4.40322477 L0.89131128,4.45169723 L1.55989957,5.41666667 Z" fill="currentColor"></path>
                                </svg>
                            </div>
                            <span class="street-middle"></span>
                            <span class="cloud"></span>
                            <span class="cloud two"></span>
                        </div>
                    </label>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
            <div>
            
            </div>
        </div>
        @endcan
    
       <div id="profile-other-content" class="container">

       <button data-id="questions-section" class="toggle selected">Questions</button>
        <button data-id="answers-section" class="toggle">Answers</button>
        <button data-id="comments-section" class="toggle">Comments</button>


       <div id="questions-section" class="toggle-section">
            @if (Auth::id() === $user->id)
                <h3>Your Questions</h3>
            @else
                <h3>Questions by {{ $user->name }}</h3>
            @endif

            @if ($questions->isEmpty())
                <p>No questions found.</p>
            @else
                <ul id="profile-question-list" class="container">
                @each('partials.questionList', $questions, 'question')
                </ul>
            @endif
        </div>

        <div id="answers-section" class="toggle-section hide">
            @if (Auth::id() === $user->id)
                <h3>Your Answers</h3>
            @else
                <h3>Answers by {{ $user->name }}</h3>
            @endif

            @if ($answers->isEmpty())
                <p>No answers found.</p>
            @else
                <ul class="answers container">
                @each('partials.answerList', $answers, 'answer')
                </ul>
            @endif
        </div>

        <div id="comments-section" class="toggle-section hide" >
            @if (Auth::id() === $user->id)
                <h3>Your Comments</h3>
            @else
                <h3>Comments by {{ $user->name }}</h3>
            @endif

            @if ($comments->isEmpty())
                <p>No comments found.</p>
            @else
                <ul class="container">
                    @each('partials.commentList', $comments, 'comment')
                </ul>
            @endif
        </div>

    </div>
</section>

<!-- added here as this logic is too simple to store in general js file -->
<script>
document.querySelectorAll('button.toggle').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('.toggle-section').forEach(section => {
                section.classList.add('hide');
            });
            document.querySelectorAll('button.toggle').forEach(btn => {
                btn.classList.remove('selected');
            });
            this.classList.add('selected');
            const sectionId = this.getAttribute('data-id');
            document.getElementById(sectionId).classList.remove("hide");
        });
    });

</script>
@endsection
