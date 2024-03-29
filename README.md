# Hierarchy

[![PHP Quality Assurance](https://github.com/Brain-WP/Hierarchy/actions/workflows/php-qa.yml/badge.svg?branch=master)](https://github.com/Brain-WP/Hierarchy/actions/workflows/php-qa.yml)
[![codecov.io](https://img.shields.io/codecov/c/github/Brain-WP/Hierarchy.svg?style=flat-square)](http://codecov.io/github/Brain-WP/Hierarchy?branch=master)
[![license](https://img.shields.io/packagist/l/brain/hierarchy.svg?style=flat-square)](http://opensource.org/licenses/MIT)
[![release](https://img.shields.io/github/release/Brain-WP/Hierarchy.svg?style=flat-square)](https://github.com/Brain-WP/Hierarchy/releases/latest)

Representation of the WordPress template hierarchy with PHP objects.

---

## TOC

- [What / Why?](#what--why)
- [Template Hierarchy Representation](#template-hierarchy-representation)
    - [Filter the Hierarchy](#filter-the-hierarchy)
- [Template Resolution](#template-resolution)
    - [Template Resolution Example](#template-resolution-example)
- [Introducing `QueryTemplate`](#introducing-querytemplate)
- [Template content is returned](#template-content-is-returned)
- [Edit template content before to output](#edit-template-content-before-to-output)
- [Template Finders](#template-finders)
    - [`Finder\ByFolders`](#finderbyfolders)
        - [Custom file extensions](#custom-file-extensions)
    - [`Finder\BySubfolder`](#finderbysubfolder)
    - [`Finder\Localized`](#finderlocalized)
    - [`Finder\SymfonyFinderAdapter`](#findersymfonyfinderadapter)
    - [`Finder\ByCallback`](#finderbycallback)
- [Core Filters for Template Loading](#core-filters-for-template-loading)
- [Introducing Template Loaders](#introducing-template-loaders)
    - [`Loader\FileRequire`](#loaderfilerequire)
    - [Aggregate Loaders](#aggregate-loaders)
    - [`Loader\Cascade`](#loadercascade)
    - [`Loader\ExtensionMap`](#loaderextensionmap)
- [`QueryTemplate` Usage Example: Loading and Rendering Mustache Templates](#querytemplate-usage-example-loading-and-rendering-mustache-templates)
- [Requirements](#requirements)
- [Installation](#installation)
- [License](#license)

-----------


## What / Why?

For every frontend request, WordPress runs a query, and then loads a template file depending on the query.

The map query => template follows rules defined in the [**template hierarchy**](https://developer.wordpress.org/themes/basics/template-hierarchy/#the-template-file-hierarchy).

However, given a query object, there's **no way** to programmatically:

- know which template WordPress will load
- know which templates WordPress will search
- apply the same "query-to-template resolution" to a query

**This library provides a way to do the 3 things listed above.**


## Template Hierarchy Representation

Given a query, this library provides a template hierarchy representation in form of a PHP array:

Example:

```php
// we will show template hierarchy for the main query
global $wp_query; 

$hierarchy = new Brain\Hierarchy\Hierarchy();

var_export($hierarchy->hierarchy($wp_query));
```

Assuming we're visiting an URL like `example.com/category/foo/page/2`, and the category ID
for the term "foo" is 123, the output of code above is:

```php
array(
  'category' => array('category-foo', 'category-123', 'category'),
  'archive' => array('archive'),
  'paged' => array('paged'),
  'index' => array('index'),
);
```

And if you compare that array with the [visual overview of template hierarchy](https://developer.wordpress.org/files/2014/10/template-hierarchy.png)
you can verify what's above is an accurate representation of the template hierarchy for a category query.


## Template Resolution

If the question you want to answer is:

> Which templates WordPress will try to find for this query?

It can be simply answered using the `Hierarchy::templates()` method:

```php
// we will target the main query
global $wp_query; 

$hierarchy = new Brain\Hierarchy\Hierarchy();

var_export($hierarchy->templates($wp_query));
```

Assuming the same query as above, the output will be:

```php
array(
  'category-foo',
  'category-123',
  'category',
  'archive',
  'paged',
  'index',
);
```

That is the list of templates WordPress will search, in the same order that WordPress will use.

### Template Resolution Example

For this example, I will assume that a theme has template files stored in `/templates` subfolder and
using `.phtml` as file extension.

All the code necessary to load those templates according to template hierarchy is the following:

```php
add_action('template_redirect', function() {

    $templates = (new Brain\Hierarchy\Hierarchy())->templates();
    
    foreach($templates as $template) {
      $path = get_theme_file_path("/templates/{$template}.phtml");
      if (file_exists($path)) {
         require $path;
         exit();
      }
    }
});
```

The example above works, and is just an example of what you can do with this library.

However, for the purpose to load templates, this library provides a specific class: `QueryTemplate`.


## Introducing `QueryTemplate`

`QueryTemplate` class makes use of the `Hierarchy` class to get a list of templates to search, then
it looks for those templates and loads the first found.

Example:

```php
add_action('template_redirect', function(): void {

    global $wp_query;
    
    $queryTemplate = new \Brain\Hierarchy\QueryTemplate();
    echo $queryTemplate->loadTemplate($wp_query);
    
    exit();
    
});
```

The code above does exactly what WordPress does: the proper template is found searching in the theme 
folder and in _parent theme_ folder (if current theme is a child theme) then the first template
found is loaded and its content is printed to page.

Please note how template content is **returned** by `QueryTemplate::loadTemplate()`, so `echo` is
necessary to actually display page content.

However, it is **just the default behavior**, and it can be customised.


## Template Finders

By default, `QueryTemplate` class, searches for templates in theme (and parent theme, if any)
folder, just like WordPress does.

However, it is possible to use a different "template finder" class to do something different.

All the template finder classes have to implement the `Brain\Hierarchy\Finder\TemplateFinder` interface.

The library comes with a few classes implementing that interface, and of course, it is possible to
write a custom one.

### `Finder\ByFolders`

The class `Brain\Hierarchy\Finder\ByFolders` can be used to search for templates in some 
**arbitrary** folders, instead of theme and parent theme folders.

Example:

```php
add_action('template_redirect', function(): void {

    $finder = new \Brain\Hierarchy\Finder\ByFolders([
       __DIR__,
       get_stylesheet_directory(),
       get_template_directory(),
    ]);
    
    $queryTemplate = new \Brain\Hierarchy\QueryTemplate($finder);  
      
    echo $queryTemplate->loadTemplate();
    exit();
    
});
```

The snippet above will search for templates in the _current folder_ and if templates are not found
there, they are searched in theme and parent theme folders.

#### Custom file extensions

`Finder\ByFolders` class, by default, searches for files with `.php` extension, but it is
possible to use different file extensions, by passing them as a second constructor argument (either
a string or an array of strings):

```php
// This will look for *.phtml files.
$phtml_finder = new \Brain\Hierarchy\Finder\ByFolders(
    [get_stylesheet_directory(), get_template_directory()],
    'phtml'
);

// This will look for Twig files first, and fall back to standard PHP files if
// no matching Twig file was found.
$twig_finder = new \Brain\Hierarchy\Finder\ByFolders(
    [get_stylesheet_directory(), get_template_directory()],
    'twig',
    'php'
);
```

### `Finder\BySubfolder`

This template finder class is very similar to `Brain\Hierarchy\Finder\ByFolders`, however it looks 
for templates is a specific subfolder of theme (and parent theme) and use theme (and parent theme)
folder as fallback:

```php
add_action('template_redirect', function(): void {

    $finder = new \Brain\Hierarchy\Finder\BySubfolder('templates');

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate($finder);

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

Using code above the templates are searched, in order, in:

- `/path/to/wp-content/child-theme/templates/`
- `/path/to/wp-content/parent-theme/templates/`
- `/path/to/wp-content/child-theme/`
- `/path/to/wp-content/parent-theme/`

`Finder\BySubfolder`, just like `Finder\ByFolders`, accepts (a variadic number of) custom file 
extensions from the second constructor argument.

### `Finder\Localized`

This finder class works in combination with another finder, and allows loading templates based on
the current locale:

```php
add_action('template_redirect', function(): void {

    $foldersFinder = new \Brain\Hierarchy\Finder\ByFolders();

    $finder = new \Brain\Hierarchy\Finder\Localized($foldersFinder);

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate($finder);

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

Assuming the current locale is `it_IT`, using code above, the templates are searched, in order, in:

- `/path/to/wp-content/child-theme/it_IT/`
- `/path/to/wp-content/parent-theme/it_IT/`
- `/path/to/wp-content/child-theme/it/`
- `/path/to/wp-content/parent-theme/it/`
- `/path/to/wp-content/child-theme/`
- `/path/to/wp-content/parent-theme/`

### `Finder\SymfonyFinderAdapter`

This class allows to use the [Symfony Finder Component](http://symfony.com/doc/current/components/finder.html)
to find templates:

```php
add_action('template_redirect', function() {

    $symfonyFinder = new \Symfony\Component\Finder\Finder();
    $symfonyFinder = $symfonyFinder->files()->in(__DIR__);

    $finder = new \Brain\Hierarchy\Finder\SymfonyFinderAdapter($symfonyFinder);

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate($finder);

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

### `Finder\ByCallback`

This class can be used to easily integrate 3rd party different loaders with `QueryTemplate` class.

In fact, you need to provide an arbitrary callback that will be called to find templates.

The callback will receive the template name without file extension, e.g. `index` and has to return
the full path of the template if found, or an empty string if the template is not found.

Example:

```php
add_action('template_redirect', function(): void {

    $callback = fn(string $tpl): string => realpath(__DIR__ . "{$template}.php") ?: '';

    $finder = new \Brain\Hierarchy\Finder\ByCallback($callback);

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate($finder);

    echo $queryTemplate->loadTemplate();
    exit();
} );
```


## Core Filters for Template Loading

When WordPress searches for a template in `template-loader.php`, it triggers different filters in
the form of [`{$type}_template`](https://developer.wordpress.org/reference/hooks/type_template/); 
examples are *'single_template'*. *'page_template'* and so on.

Moreover, the found template passes through the [*'template_include'*](https://developer.wordpress.org/reference/hooks/template_include/)
filter.

By default, **`QueryTemplate::loadTemplate()` applies same filters**, to maximize compatibility with
core behavior.

That happens no matter the template finder is used.

However, by passing `false` as second argument to the method it will stop to apply those core
filters.


## Introducing Template Loaders

After a template is found with any of the finder classes, `QueryTemplate` has to "load" it.

By default, loading is just a `require` wrapped by `ob_start()` / `ob_get_clean()` so that the
template content is returned as-is.

However, is it possible to *process* the template in some ways, for example, by using a **template
engine**.

Custom template loaders have to implement `Brain\Hierarchy\Loader\Loader` interface, that has just
one method: `load()`, that receives the full path of the template and have to **return** the 
template content.

Template loaders can be passed as second constructor argument to `QueryTemplate`.

### `Loader\FileRequire`

This is the a loader class that ships with the library, and it provides the default behavior.

### Aggregate Loaders

Aggregate loaders uses different "inner" loaders to load templates.

Aggregate loaders have to implement the interface `Brain\Hierarchy\Loader\Aggregate` that has two
methods:

- `addLoader(Loader\TemplateLoader $loader, callable $predicate)`
- `addLoaderFactory(callable $loaderFactory, callable $predicate)`

The first is used to add a template loader instance.
The second is used to add a factory that once called will return a template loader instance.

Both methods accept as second argument a "predicate": a callback that will receive the path of the
template file to load, and will return a boolean.

When the predicate returns `true`, the related loader is used to load the template.

### `Loader\Cascade`

`Loader\Cascade` is a simple implementation of an aggregate loader, where the predicates are 
evaluated in the same order they are added (FIFO).

### `Loader\ExtensionMap`

`Loader\ExtensionMap` is another aggregate loader implementation shipped with Hierarchy.

It is used to load different loaders based on template file extension.

It requires an extensions-to-loaders "map" to be passed to constructor.

The map keys are the template file extensions, the values are the loader to be used.

Loaders can be passed as:

- template loader instances
- template loader fully qualified class names
- factory callbacks that once called return template loader instances

The same loader can be used for multiple file extensions, using as map key a string composed by many
file extensions separated by a pipe `|`.

Example:

```php
$loader = new Loader\ExtensionMap([
    'php|phtml' => new Loader\FileRequire(),
    'mustache'  => fn() => new MyMustacheAdapter(new Mustache_Engine),
    'md' => MyMarkdownRenderer::class
]);
```

## `QueryTemplate` Usage Example: Loading and Rendering Mustache Templates

The following will present all the code necessary to find and render [mustache](https://github.com/bobthecow/mustache.php)
templates according to WordPress template hierarchy.

```php
namespace My\Theme;

use Brain\Hierarchy\{Finder, Loader, QueryTemplate};

class MustacheTemplateLoader implements Loader\Loader
{
   private $engine;

   public function __construct(\Mustache_Engine $engine)
   {
      $this->engine = $engine;
   }

   public function load(string $templatePath): string
   {
        // let's use a filter to build some context for the template
        $data = apply_filters('my_theme_data', ['query' => $GLOBALS['wp_query'], $templatePath);

        return $this->engine->render(file_get_contents($templatePath), $data);
   }
}

add_action('template_redirect', function() {
    if (!QueryTemplate::mainQueryTemplateAllowed()) {
        return;
    }

    $queryTemplate = new QueryTemplate(
        // will look for "*.mustache" templates in theme's "/templates" subfolder
        new Finder\BySubfolder('templates', 'mustache'),
        // the loader class defined above
        new MustacheTemplateLoader(new \Mustache_Engine())
    );

    // 3rd argument of loadTemplate() is passed by reference, and set to true if template is found
    $content = $queryTemplate->loadTemplate(null, true, $found);
    // if template was found, let's output it and exit, otherwise WordPress will continue its work
    $found and die($content);
});
```


## Requirements

Hierarchy requires **PHP 7.1.3+** and [Composer](https://getcomposer.org/) to be installed.


## Installation

Best served by Composer, available on Packagist with name [`brain/hierarchy`](https://packagist.org/packages/brain/hierarchy).


## Migration from version 2.*

The library logic in version 3 is not changed, but now all classes use type declaration, and some of
them has been renamed.

Libraries based on Hierarchy which are implementing its loader/finder interfaces will have some work 
to do to rename classes and add type declaration.

The class `FileExtensionPredicate` has a slightly changed signature.

Libraries which are only using `Hierarchy` class, should work without any change, even if the methods 
`getHierarchy()` and `getTemplates()` are now _deprecated_ in favor of, respectively, `hierarchy()` 
and `templates()`, but old method will not removed in any version 3.* release.


## License

Hierarchy is released under MIT.
