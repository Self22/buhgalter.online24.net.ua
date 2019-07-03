@php
    header("HTTP/1.0 404 Not Found");
@endphp
<html>
<head>
    <link href='//fonts.googleapis.com/css?family=Lato:100' rel='stylesheet' type='text/css'>


    <style>
        body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
            font-family: 'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 42px;
            margin-bottom: 40px;
        }
    </style>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" type="image/x-icon" href="{{url('/img/favicon.png')}}">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="container container-err">
    <header class="main__header">
        <a href="/" class="headerimg__cont">
            <img src="{{ asset('img\logo_hg.png') }}" alt="">
        </a>
    </header>
    <div class="content">

        <div class="title">Страница не найдена</div>
        <a href="/">Вернуться на главную</a>
    </div>
</div>
</body>
</html>




