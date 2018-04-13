## ![Warning](https://placehold.it/35/f03c15/ffffff?text=!) Warning: work in progress

Miravel is currently a work in progress and cannot yet be used in real projects. Most things described below are not yet implemented. When a usable version is released, this warning will go away.

# Miravel
A theme engine for the Laravel framework, with its own theme repository, and with the ability for everyone to develop and contribute their own themes.

The full documentation is available at: https://miravel.io/docs

### Features:

- Miravel comes with a default theme that already contains most popular layouts: fluid, fixed width, one- and two-column, sticky footer, form centered on a screen etc.
- Themes consists of skins, layouts, elements (blocks) and templates. All of them can be used independently and even with conjunction with components from other themes.
- Miravel is very inobtrusive and can co-exist with any other frontend logic, being called / used only when needed. You can limit its use for only a couple of layouts/elements should you so decide.
- Offers special Blade tags ```@element```, ```@js```, ```@css```, etc.
- Whenever you need, override the entire theme or any of its components (css, js, view files) separately in your app.
- Command line interface offers one-liners for many complex operations, e.g. "install this theme and use its 'home page' template as my home page".
- Templates are built with Blade and around php variables, so in many cases all you need is just to eloquent-pull data into a variable with proper name.
- But of course you can easily add custom logic to elements by creating your own class for an element.
- Themes can inherit (extend / override) each other.
- Miravel leverages Assetic to easily orchestrate asset build pipeline (such as css/js preprocessing, concatenation, minification etc).
- And also has tools for creating a very fine-grained asset build for every specific page of your app. Some pages do not need jQuery? No problem, create a jquery-less ```app.js``` just for these pages. Miravel automates most of these routines.
- Setting a theme as "Global" automatically gives you the error pages and basic email templates that are consistent with the chosen theme/skin.
- It is just a Laravel miracle.

### Usage

Quick dive into using Miravel with your Laravel project:

#### Pull in the package

```bash
composer require miravel/miravel
```

#### Laravel 5.4 or earlier, or Laravel 5.5 without auto-discovery

Below Laravel 5.5, package auto-discover is not available so some extra steps are also necessary:

Add the provider and facade to your ```config/app.php```

```php
'providers' => [
//...
Miravel\ThemeServiceProvider::class,
//...
],
```

```php
'aliases' => [
//...
'Miravel' => Miravel\Facade::class,
//...
],
```

#### Use a template

This line will replace your ```welcome.blade.php``` with a template from Miravel's default theme:

```bash
php artisan miravel:use default.home-page --as=welcome
```

And go visit your page in browser to see the change.

#### Use a layout

You can use Miravel's theme layouts as you traditionally do that by extending them inside your blade views:

```
@extends('miravel::default.one-column')

@section('content')
Awesome stuff here
@endsection
```

Your awesome stuff will be placed inside the "one-column" layout from the "default" theme.

#### Use an element

Inside your view:

```php
// Assuming "$items" is holding something like this
// [['text' => 'Home', 'url' => '/'], ['text' => 'About Us', 'url' => '/about']]
// this will render the "menu" template from the "default" theme with these items.

@element('default.menu', $items);
```

#### Override any layout or element

To start playing with the view file, just copy it from the ```vendor``` folder to ```app/resources/views/vendor/miravel/theme-name```. 

E.g. to override the "fixed" layout from the "default" theme, copy:

_vendor/miravel/miravel/resources/themes/**default/layouts/fixed/view.blade.php**_

to

_app/resources/vendor/miravel/**default/layouts/fixed/view.blade.php**_

Miravel offer an artisan one-liner for this operation to somewhat shorten your typing:

```bash
php artisan miravel:clone default.layouts.fixed.view
```

or to pull the entire layout (with all styles and scripts)

```bash
php artisan miravel:clone default.layouts.fixed
```

to pull the entire theme

```bash
php artisan miravel:clone default
```

You get the idea.
