<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ratboard</title>
    <meta name="description" content="@yield('meta_description', 'Laravel 5 Boilerplate')">
    <meta name="author" content="@yield('meta_author', 'Anthony Rappa')">

    <!-- Scripts -->
    <script src="{{ asset('js/ratboard.js') }}" defer></script>

    @yield('meta')

    {{-- See https://laravel.com/docs/5.5/blade#stacks for usage --}}
    @stack('before-styles')

    <!-- Check if the language is set to RTL, so apply the RTL layouts -->
    <!-- Otherwise apply the normal LTR layouts -->


    @stack('after-styles')
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">

    <!-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> -->

    <style type="text/css">
    /* ---- reset ---- */
    canvas{ display: block; vertical-align: bottom; }
    #particles-js{
      position:absolute; width: 100%; height: 100%;
      background-image: linear-gradient(to bottom, #00AAFF, #021449);
    }
    #preloader {
        overflow: hidden;
        background-color: #fff;
        height: 100%;
        left: 0;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 999999;
    }

    .colorlib-load {
        -webkit-animation: 2000ms linear 0s normal none infinite running colorlib-load;
        animation: 2000ms linear 0s normal none infinite running colorlib-load;
        background: transparent none repeat scroll 0 0;
        border-color: #dddddd #dddddd #021449;
        border-radius: 50%;
        border-style: solid;
        border-width: 2px;
        height: 40px;
        left: calc(50% - 20px);
        position: relative;
        top: calc(50% - 20px);
        width: 40px;
        z-index: 9;
    }

    @-webkit-keyframes colorlib-load {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes colorlib-load {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }
        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
    </style>
</head>

<body>
  <!-- Preloader Start -->
  <div id="preloader">
      <div class="colorlib-load"></div>
  </div>
    <div class="" id="app"></div>

    <script src="/js/jquery-3.4.1.min.js" charset="utf-8"></script>
    <script type="text/javascript">
      $(window).on('load', function(){
        $('#preloader').fadeOut('slow', function () {
            $(this).remove();
        });
      });
    </script>
</body>
</html>
