<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    {{ setlocale(LC_TIME, '') }}

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <style>
        html, body {
            background-color: #fff;
            color: #636b6f;
            font-family: 'Raleway', sans-serif;
            font-weight: 100;
            height: 100vh;
            margin: 0;
        }

        .full-height {
            height: 100vh;
        }

        .flex-center {
            align-items: center;
            display: flex;
            justify-content: center;
        }

        .position-ref {
            position: relative;
        }

        .top-right {
            position: absolute;
            right: 10px;
            top: 18px;
        }

        .content {
            text-align: center;
        }

        .title {
            font-size: 84px;
        }

        .links > a {
            color: #636b6f;
            padding: 0 25px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .1rem;
            text-decoration: none;
            text-transform: uppercase;
        }

        .m-b-md {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
<div class="flex-center position-ref full-height">
    @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <a href="{{ url('/home') }}">Home</a>
            @else
                <a href="{{ route('login') }}">Login</a>
                <a href="{{ route('register') }}">Register</a>
                @endauth
        </div>
    @endif

    <div class="content">
        <div class="container-fluid">
            <div class="animate fadeIn">
                <div class="col-md-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">Posts</div>

                        <div class="panel-body">

                            <br/>


                            <br/>
                            <div class="table-responsive">

                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Href</th>
                                        <th>Anchor</th>
                                        <th>Site</th>
                                        <th>Time</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($links as $link)
                                        <tr>
                                            <td>{{ $link->href }}</td>
                                            <td>{{ $link->anchor }}</td>
                                            <td>{{ $link->site }}</td>
                                            <td><?php


                                                $date = $link->created_at->formatLocalized('%A %d %B %Y');
                                                $anchor = mb_convert_encoding($date,'UTF-8');
                                                    echo($date);
                                                ?></td>
                                        </tr>




                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- Pagination -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
</div>
</body>
</html>
