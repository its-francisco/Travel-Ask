<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="/images/icon.ico" type="image/x-icon">
        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title') | {{ config('app.name', 'Travel&Ask') }}</title>
        <!-- Styles -->
        <link href="{{ url('css/app.min.css') }}" rel="stylesheet">
        <link href="{{ url('css/print.css') }}" media="print" rel="stylesheet">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <script src="{{ url('js/app.min.js') }}" defer></script>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
        <!-- NOTIFICATIONS: -->

    </head>
    <body>
        <script>
            const savedTheme = localStorage.getItem('theme') || 'light'; 
            if (savedTheme === 'dark') document.querySelector("body").classList.add("dark");
        </script>
        <a href="#page-content" class="skip-link">Skip navigation</a>
        @yield('skipLinks')
        <main>
            <header>
                <div id="messages">
                @if($errors->any())
                    @foreach ($errors->all() as $error)
                        <article class="error">
                            <i class="material-symbols-outlined" title="Error"> error </i>
                            <p>{{$error}}</p>
                            <div class="message-progress"></div>
                            <div class="airplane-icon">✈</div>
                        </article>
                    @endforeach
                @endif
                @if(session('success'))
                        <article class="success">
                            <i class="material-symbols-outlined" title="Success"> check_circle </i>
                            <p> {{ session('success') }}</p>
                            <div class="message-progress"></div>
                            <div class="airplane-icon">✈</div>
                        </article>
                @endif
                </div>
                <div class="banner">
                    <div>
                        <a href="{{ url('/') }}"><img width="127" id="white-logo" height="50" src="/images/white_logo.png" alt="logo">
                        <img width="127" id="dark-logo" height="50" src="/images/dark_logo.png" alt="logo"></a>
                    </div>   
                <div>
                        <span class="hamburger material-symbols-outlined" id="menu-mobile-hb" title="Menu">menu</span>
                        <nav><ul>
                            <li class="{{ Request::routeIs('index') ? 'currentPage' : '' }}"><a href="{{route('index')}}">Home</a></li>
                            <li class="{{ Request::routeIs('search') ? 'currentPage' : '' }}"><a href="{{route('search')}}">Questions</a></li>
                            <li class="{{ Request::routeIs('about') ? 'currentPage' : '' }}"><a href="{{route('about')}}">About</a></li>

                        @auth
                            <li class="{{ Request::routeIs('feed') ? 'currentPage' : '' }}"><a href="{{route('feed')}}">Feed</a></li>
                            <li class="{{ Request::routeIs('question.create') ? 'currentPage' : '' }}"><a href="{{ route('question.create') }}"><span class="material-symbols-outlined" title="Add a Question">add</span></a> </li>
                            <li><button class="button-icon material-symbols-outlined" id="notification-inbox-buttonpc" title="Inbox">inbox</button></li>
                            @can('accessAdmin', App\Models\User::class)
                                <li class="{{ Request::routeIs('admin.main') ? 'currentPage' : '' }}"><a href="{{ route('admin.main') }}"><span class="material-symbols-outlined" title="Admin Panel">admin_panel_settings</span></a></li>
                            @endcan
                            <li><a href="{{ url('users/' . Auth::user()->id) }}" title="Profile Page"><img class="profile_photo" src="{{ Auth::user()->getProfileImage() }}" alt="profile_photo"></a></li> <!-- Link para o perfil -->
                            <li><a href="{{ url('/logout') }}"><span class="material-symbols-outlined" title="Logout">logout</span></a></li>
                        @else
                        <li class="{{ Request::routeIs('question.create') ? 'currentPage' : '' }}"><a href="{{ route('question.create') }}"><span class="button-icon material-symbols-outlined" title="Add a Question" onclick="event.preventDefault(); openLoginOverlay();">add</span></a> </li>
                            <li><a class="button" href="{{ url('/login') }}"> Login</a></li>
                        @endauth
                        <li><span class="material-symbols-outlined search-header-btn" title="Search">search</span></li>
                        <li><button class="material-symbols-outlined dark-mode-btn button-icon" title="Dark and Light Mode">dark_mode</button></li>

                        </ul></nav>
                    </div>
                </div>
                <div id="mobile-menu">
                    <nav><ul>
                            <li class="{{ Request::routeIs('index') ? 'currentPage' : '' }}"><a href="{{route('index')}}">Home</a></li>
                            <li class="{{ Request::routeIs('search') ? 'currentPage' : '' }}"><a href="{{route('search')}}">Questions</a></li>
                            <li class="{{ Request::routeIs('about') ? 'currentPage' : '' }}"><a href="{{route('about')}}">About</a></li>

                            @auth
                                <li class="{{ Request::routeIs('feed') ? 'currentPage' : '' }}"><a href="{{route('feed')}}">Feed</a></li>
                                <li class="{{ Request::routeIs('question.create') ? 'currentPage' : '' }}"><a href="{{ route('question.create') }}"><span class="material-symbols-outlined" title="Add a Question">add</span></a> </li>
                                <li><span class="material-symbols-outlined" id="notification-inbox-buttonmobile" title="Inbox">inbox</span></li>
                                @can('accessAdmin', App\Models\User::class)
                                    <li class="{{ Request::routeIs('admin.main') ? 'currentPage' : '' }}"><a href="{{ route('admin.main') }}"><span class="material-symbols-outlined" title="Admin Panel">admin_panel_settings</span></a></li>
                                @endcan
                                <li><a href="{{ url('users/' . Auth::user()->id) }}" title="Profile Page"><img class="profile_photo" src="{{ Auth::user()->getProfileImage() }}" alt="profile_photo"></a></li> <!-- Link para o perfil -->
                                <li><a href="{{ url('/logout') }}"><span class="material-symbols-outlined" title="Logout">logout</span></a></li>

                            @else
                                <li><a class="button" href="{{ url('/login') }}">Login</a></li>
                            @endauth
                            <li><span class="material-symbols-outlined search-header-btn" title="Search">search</span></li>
                            <li><span class="material-symbols-outlined dark-mode-btn" title="Dark and Light Mode">dark_mode</span></li>

                        </ul></nav>
                </div>
            </header>
            <dialog class="search-overlay" id="search-overlay">
                <div>
                    <div>
                        <form id="search-overlay-form" action="{{ route('search') }}" method="GET">
                            <label>
                            <input type="text" name="query" placeholder="Search here..." autocomplete="off" value="{{ isset($query) ? $query : ''}}">
                            </label>
                            <button class="button-icon" type="submit"><span class="material-symbols-outlined" title="Search">search</span></button>
                        </form>
                        <div id="outside-overlay"></div>
                    </div>
                </div>
            </dialog>
            <div id="notifications-container"></div>
            <div id="notification-inbox">
                <span>close</span>
                <div id="notification-inbox-content">

                </div>
            </div>
            <div id="infohelper-div">
                <span class="material-symbols-outlined">help</span>
                <div id="infohelper-div-content">
                </div>
            </div>

            <dialog class="overlay" id="overlay-login">
                <div id="outside-login-overlay">
                    <div>
                        <form method="dialog">
                            <button class="button-icon material-symbols-outlined" title="Close">close</button>
                        </form>
                        <form class="overlay-login-form" method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="overlay" value="1">
                            <h3>Please Login before continuing</h3>
                            <label>E-mail<abbr class="requiredField" title="mandatory field">*</abbr>
                            <input type="email" placeholder="Enter your email here..." name="email" value="{{ old('email') }}" required autofocus>
                            </label>
                            <label>Password <abbr class="requiredField" title="mandatory field">*</abbr>
                            <input placeholder="Enter your password here..." type="password" name="password" required>
                            </label>
                            <button type="submit">Login</button>
                            <a class="button button-outline" href="{{ route('register') }}">Register</a>
                            <a href="{{ route('pass.recover') }}">Forgot your password?</a>
                            @include('partials.google')

                        </form>
                    </div>
                </div>
            </dialog>
            <section id="page-content">
                @yield('content')
            </section>
            <footer>
                <a href="{{ url('/') }}"><img width="150" src="/images/banner-clean.png" alt="footer_banner"></a>
                <div class="footer-links">
                    <a href="{{ route('index') }}">Home</a>
                    <a href="{{ route('search') }}">Questions</a>
                    <a href="{{ route('about') }}">About</a>
                @auth
                    <a href="{{ route('feed') }}">Feed</a>
                @endauth

                </div>
                <div class="footer-contact">
                    <p>Email: support@travelandask.com</p>
                    <p>Phone: +1 (123) 456-7890</p>
                    <p>Address: s/n Somewhere over the rainbow, Porto, Portugal</p>
                    <p>&copy; All rights reserved</p>
                </div>
            </footer>
        </main>
        <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
        <!-- PUSH NOTIFICATIONS -->
        <script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>
        <script>
            function createNotification(data, theme, question_id){
                let notificationDiv = document.createElement('div');
                notificationDiv.classList.add('app-notification');


                let closeButton = document.createElement('span');
                closeButton.style.cursor = "pointer";
                closeButton.textContent = 'close';
                closeButton.classList.add("material-symbols-outlined");
                closeButton.classList.add('close-button');
                closeButton.title = "Close";
                notificationDiv.appendChild(closeButton);

                let messageP = document.createElement('p');
                messageP.textContent = data;

                let linkA = document.createElement('a');
                linkA.href = '/questions/' + question_id;
                linkA.textContent = 'View Post';

                notificationDiv.appendChild(messageP);
                notificationDiv.appendChild(linkA);



                closeButton.onclick = function() {
                    notificationDiv.remove();
                };


                document.getElementById('notifications-container').appendChild(notificationDiv);
            }
            // app notif

            // Enable pusher logging - don't include this in production
            //Pusher.logToConsole = true;

            let pusher = new Pusher('8cd033eccac785538d07', {
                cluster: 'eu',
                encrypted: true,
            });

            // we have to run pusher.signin() to make the userAuthentication run!

            @auth
            let privatechannel = pusher.subscribe('user.{{Auth::user()->id}}');
            privatechannel.bind('notification', function(data) {
                createNotification(data['message'], data['theme'], data['question_id']);
                console.log("NEW NOTIFICATION");
            });
            @endauth

            // to unsubscribe - it checks headers
            fetch(window.location.href, { method: 'HEAD' })
                .then(response => {
                    if (response.headers.get('X-Logged-Out')) {
                        let userId = response.headers.get('X-User-ID');
                        pusher.unsubscribe('user.' + userId);
                    }
                });

            // push
            const beamsClient = new PusherPushNotifications.Client({
                instanceId: '677e4aef-0c71-4a1f-bdab-14d416de61a9',
            });

            beamsClient.start()
                .then(() => beamsClient.addDeviceInterest('general'))
                .then(() => console.log('Successfully registered and subscribed!'))
                .catch(console.error);
        </script>
    </body>
</html>