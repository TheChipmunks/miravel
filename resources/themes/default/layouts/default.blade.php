<!doctype html>
<html class="no-js" lang="{{ app()->getLocale() }}">
    <head>

@include('miravel::default.partials.head')

{!! Miravel::css() !!}

    </head>
    <body>

@yield('content')

{!! Miravel::js() !!}
    </body>
</html>
