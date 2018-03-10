@foreach(Miravel::getConfig('html.meta_tags') as $tag)
<meta {!! collect($tag)->map(function ($value, $name) { return "$name=\"$value\""; })->implode(' ') !!}>
@endforeach

<title>{{ $document['title'] or config('app.name') }}</title>

@isset($document['description'])
<meta name="description" content="{{ $document['description'] }}">
@endisset

@isset($document['keywords'])
<meta name="keywords" content="{{ $document['keywords'] }}">
@endisset

{!! Miravel::css('styles') !!}
{!! Miravel::js('js/head') !!}
