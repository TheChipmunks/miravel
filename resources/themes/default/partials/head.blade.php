<meta charset="{{ config('miravel.html.charset', 'utf-8') }}">

<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

<title>{{ $document['title'] or config('app.name') }}</title>

@isset($document['description'])
<meta name="description" content="{{ $document['description'] }}">
@endisset

@isset($document['keywords'])
<meta name="keywords" content="{{ $document['keywords'] }}">
@endisset

{!! Miravel::css('styles') !!}
{!! Miravel::js('js/head') !!}
