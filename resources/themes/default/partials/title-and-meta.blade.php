<title>{{ $document['title'] or config('app.name') }}</title>

@isset($document['description'])
<meta name="description" content="{{ $document['description'] }}">
@endisset

@isset($document['keywords'])
<meta name="keywords" content="{{ $document['keywords'] }}">
@endisset

