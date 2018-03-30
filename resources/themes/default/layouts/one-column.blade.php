<!doctype html>
<html class="no-js layout-default{{ isset($htmlclass) ? " $htmlclass" : '' }}" lang="{{ app()->getLocale() }}">
    <head>

@themeinclude('partials.head')

    </head>
    <body{{ isset($bodyclass) ? " class=\"$bodyclass\"" : '' }}>

<header>
    <div class="container-fluid">
@yield('header')
    </div>
</header>

<div class="container">

@yield('content')

</div>

<footer>
    <div class="container-fluid">
@yield('footer')
    </div>
</footer>

    </body>
</html>
