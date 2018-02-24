<div class="collapse navbar-collapse" id="app-navbar-collapse">
@if(count($data))
<ul class="nav navbar-nav">
    @foreach($data as $item)
        @php
            $cssClasses = [];
            if ($loop->first) $cssClasses[] = 'first';
            if ($loop->last) $cssClasses[]  = 'last';
        @endphp
        <li class="{{ implode(' ', $cssClasses) }}">
            <a href="{{ $element->get($item, 'url') }}">{{ $element->get($item, 'text') }}</a>
        </li>
    @endforeach
</ul>
@endif
</div>
