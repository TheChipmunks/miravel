@extends('miravel::child.layouts.extended')

@section('content')

@element('child.navbar-menu', [
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Login', 'url' => '/login']
], [
    'class' => 'inline',
    'property_map' => [
        'text' => 'name'
    ]
])

@element('child.panel', [
    'title' => 'Dashboard', 'content' => 'hello Miravel'
])

@element('child.grid', [
    ['title' => 'Item 1'],
    ['title' => 'Item 2'],
    ['title' => 'Item 3'],
    ['title' => 'Item 4'],
    ['title' => 'Item 5'],
    ['title' => 'Item 6'],
    ['title' => 'Item 7'],
    ['title' => 'Item 8'],
    ['title' => 'Item 9'],
])

@endsection
