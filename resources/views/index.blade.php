<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <title></title>
    <meta name="description"
          content="">
    <meta name="Keywords" content="ліга законів, консультація для бухгалтерів онлайн">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
</head>
<body>
<html>
    <header class="header__index">
        <h1 class="project__name">
            Бухгалтер Онлайн24
        </h1>

        <div class="project__logo"><img src="" alt=""></div>
        <div class="header__descr"></div>
    </header>
    <main class="links__container">
        @foreach($links as $link)
            <div class="link__box">
                <div class="link__time">{{$link->time}}</div>
                <a href="{{$link->href}}" rel="nofollow" target="_blank" class="link__href">{{$link->anchor}}</a>

                <div class="link__site">{{$link->site}}</div>
                <div class="link__category">{{$link->category}}</div>
            </div>
        @endforeach
            <div class="pagination_cont">
                {{ $links->links() }}
            </div>

    </main>
    <aside class="sidebar_main"></aside>
<footer class="footer__index">

</footer>

</html>
</body>