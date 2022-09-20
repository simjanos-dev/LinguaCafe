<html>
    <head>
        <link href="{{ asset('css/kanji_print.css') }}" rel="stylesheet">
    </head>
    <body>
        @foreach ($kanjiList as $chunk)
            <div class="page">
                @foreach ($chunk as $kanji) 
                    <div class="row">
                        <div class="meaning">{{ explode('|', $kanji)[2] }}</div>
                        <div class="character stroke-order">{{ explode('|', $kanji)[0] }}</div>
                        @for ($i = 0; $i < 6; $i++)
                            <div class="character">{{ explode('|', $kanji)[0] }}</div>
                        @endfor
                    </div>
                @endforeach
            </div>
        @endforeach
    </body>
</html>