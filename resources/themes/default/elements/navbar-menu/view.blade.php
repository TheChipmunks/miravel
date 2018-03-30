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
            <a href="@prop('item.url')">@prop('item.text')</a>
        </li>
    @endforeach
</ul>
@endif
</div>
