<!doctype {{ config('miravel.html.doctype') or 'html' }}>
<html class="no-js layout-default{{ isset($htmlclass) ? " $htmlclass" : '' }}" lang="{{ app()->getLocale() }}"{{ Miravel::renderHtmlAttributes() }}>
    <head>

@themeinclude('partials.head')

    </head>
    <body class="miravel-theme-default miravel-skin-default{{ isset($bodyclass) ? " $bodyclass" : '' }}"{{ Miravel::renderBodyAttributes() }}>

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

@themeinclude('partials.bottom-scripts')

    </body>
</html>
