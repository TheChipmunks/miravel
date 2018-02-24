<!doctype html>
<html class="no-js layout-default{{ isset($htmlclass) ? " $htmlclass" : '' }}" lang="{{ app()->getLocale() }}">
    <head>

@themeinclude('partials.head')

    </head>
    <body{{ isset($bodyclass) ? " class=\"$bodyclass\"" : '' }}>
{!! Miravel::js('top') !!}

<header>
    <div class="container">
@yield('header')
    </div>
</header>

<div class="container">

@yield('content')

</div>

<footer>
    <div class="container">
@yield('footer')
    </div>
</footer>

{!! Miravel::js('bottom') !!}
    </body>
</html>
