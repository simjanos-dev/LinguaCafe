<nav class="navbar navbar-expand-sm navbar-light bg-white shadow-sm">
    <div class="container">
        @if (env('DB_DATABASE') !== 'langapp')
            <span style="margin-right: 5px; font-size: 18px; font-weight: bold; background-color: #f1ad02; color: white; border-radius: 4px; padding: 0px 4px;">DEV</span>
        @endif
        <a class="navbar-brand" href="{{ url('/') }}"><i class="fa fa-language"></i></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
        <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            @auth
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/courses') }}">Library</a>
                </li>
                <li id="vocabulary-select-dropdown" class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        Vocabulary
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ url('/vocabulary-practice/random') }}">Random</a>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/flash-card-collections') }}">Flash cards</a>
                </li>
            @endauth
        </ul>

        <ul class="navbar-nav ml-auto">
            @auth
                <li id="language-select-dropdown" class="nav-item dropdown">
                    <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                        <img src="/images/flags/{{ Auth::user()->selected_language }}" width="28" height="20"> {{ ucfirst(Auth::user()->selected_language) }}
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="{{ url('/language/japanese') }}">
                            <img src="/images/flags/japanese" width="28" height="20"> Japanese
                        </a>
                        <a class="dropdown-item" href="{{ url('/language/norwegian') }}">
                            <img src="/images/flags/norwegian" width="28" height="20"> Norwegian
                        </a>
                        <hr>
                        <ebook-reader-mode-component></ebook-reader-mode-component>
                        <hr>
                        <a class="dropdown-item" href="{{ route('logout') }}"onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </li>
            @endauth
            @guest
                @if (Route::has('login'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                    </li>
                @endif

                @if (Route::has('register'))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                    </li>
                @endif
            @endguest
        </ul>
        </div>
    </div>
</nav>