{{-- render common meta tags defined in config/miravel.php, such as viewport, charset and similar --}}
{{ Miravel::renderMetaTags() }}

{{-- include the section with title and meta keywords/description tags --}}
@themeinclude('partials.title-and-meta')

{{-- include the links to favicon locations --}}
@themeinclude('partials.favicon')

{{-- theme head styles --}}
@themeinclude('partials.head-styles')

{{-- theme head scripts --}}
@themeinclude('partials.head-scripts')
