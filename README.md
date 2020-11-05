Hierarchy
=========

> Hierarchy is package that aims to represent with PHP objects the WordPress template hierarchy.

----------

[![travis-ci status](https://img.shields.io/travis/Brain-WP/Hierarchy.svg?style=flat-square)](https://travis-ci.org/Brain-WP/Hierarchy)
[![codecov.io](https://img.shields.io/codecov/c/github/Brain-WP/Hierarchy.svg?style=flat-square)](http://codecov.io/github/Brain-WP/Hierarchy?branch=master)
[![license](https://img.shields.io/packagist/l/brain/hierarchy.svg?style=flat-square)](http://opensource.org/licenses/MIT)
[![release](https://img.shields.io/github/release/Brain-WP/Hierarchy.svg?style=flat-square)](https://github.com/Brain-WP/Hierarchy/releases/latest)

----------

# TOC

- [What / Why?](#what--why)
- [Template Hierarchy Representation](#template-hierarchy-representation)
  - [Filter the Hierachy](#filter-the-hierarchy) 
- [Template Resolution](#templates-resolution)
  - [Template Resolution Example](#templates-resolution-example)
- [Introducing `QueryTemplate`](#introducing-querytemplate)
  - [Template content is returned](#template-content-is-returned)
  - [Edit template content before to output](#edit-template-content-before-to-output)
  - [Template Finders](#template-finders)
    - [`FoldersTemplateFinder`](#folderstemplatefinder)
      - [Custom file extensions](#custom-file-extensions)
    - [`SubfolderTemplateFinder`](#subfoldertemplatefinder)
    - [`LocalizedTemplateFinder`](#localizedtemplatefinder)
    - [`SymfonyFinderAdapter`](#symfonyfinderadapter)
    - [`CallbackTemplateFinder`](#callbacktemplatefinder)
  - [Core Filters for Template Loading](#core-filters-for-template-loading)
  - [Introducing Template Loaders](#introducing-template-loaders)
    - [`FileRequireLoader`](#filerequireloader)
  - [Aggregate Loaders](#aggregate-loaders) 
    - [`CascadeAggregateTemplateLoader`](#cascadeaggregatetemplateloader) 
    - [`ExtensionMapTemplateLoader`](#extensionmaptemplateloader) 
  - [`QueryTemplate` Usage Example: Loading and Rendering Mustache Templates](#querytemplate-usage-example-loading-and-rendering-mustache-templates)
- [Requirements](#requirements)
- [Installation](#installation)
- [Updating from 0.x releases](#updating-from-0x-releases)
  - [0.x Maintenance](#0x-maintenance)
- [License](#license)

-----------

# What / Why?

Every WP developer knows that for every frontend request, WordPress runs a query and then loads a
template file depending on the query.

The choose of template is done according to rules defined in the [**template hierarchy**](https://developer.wordpress.org/themes/basics/template-hierarchy/#the-template-file-hierarchy).

For every query, there's one template.

Now, I want to ask you: **given a query object, which is the function that gives you the template**?

The answer is: such function **doesn't exist**.

The query-to-template resolution is done by WordPress requiring 
[`template_loader.php`](https://github.com/WordPress/WordPress/blob/master/wp-includes/template-loader.php#L58-L79) file
that contains a procedural succession of `if` / `elseif` that looks in template folder and try to find
a template.

It means:

 - There's no way to know which template will be used for a query before the template is loaded
 - There's no way to apply the query-to-template resolution to a query that is not the main query
 - Given a query, there's no way to know which templates WordPress will look for
 
**What this library does is to provide a way to do the 3 things listed above.**
 
 
# Template Hierarchy Representation

Given a query, this library provides a template hierarchy representation in form of a PHP array:

Example:

```php
global $wp_query; // we will show template hierarchy for the main query

$hierarchy = new Brain\Hierarchy\Hierarchy();

var_export( $hierarchy->getHierarchy($wp_query) );
```

assuming the query is generated by an url like `example.com/category/foo/page/2` and the category ID for
the term "foo" is 123, the output of code above is:

```php
array(
  'category'  => array( 'category-foo', 'category-123', 'category' ),
  'archive'   => array( 'archive' ),
  'paged'     => array( 'paged' ),
  'index'     => array( 'index' ),
);
```

And if you compare this array with the [visual overview of template hierarchy](https://developer.wordpress.org/files/2014/10/template-hierarchy.png)
you can see that is an accurate representation of the template hierarchy for a category query. 

### Filter the Hierarchy

Hierarchy provides a filter, `brain-hierarchy.branches` that allows to filter the branches that will
be used to when "resolving" a query.

The filter passes the array of branches, where each item is a **class name** of an object implementing
`BranchInterface`. The array can be modified, but Hierarchy ensures that after the filter all the items
are still an array of the same type.

Considering that changing the branch will break core compatibility (it is possible to obtain a 
completely different template hierarchy compared to the one core uses) I suggest to use this feature
only if you are sure what you are going to do.

It is also possible to avoid any filtering by instantiating `Hierachy` class with the `Hierachy::NOT_FILTERABLE`
 flag.
 
```php
$hierarchy = new Hierachy(Hierachy::NOT_FILTERABLE);
```

Note that this will prevent `Hierachy` to fire the core `{$type}_template_hierachy` filter (introduced in WP 4.7).

This hook gives the ability to filter a specific "branch" of the template hierarchy. 
See ["Make WordPress" blog post](https://make.wordpress.org/core/2016/09/09/new-functions-hooks-and-behaviour-for-theme-developers-in-wordpress-4-7/) 
for more details.

Let me just cite from there:

> It’s important to remember that the consistency of the template hierarchy in WordPress is what 
makes standardised theme structures possible. 
It’s highly recommended that you do not remove templates from the candidate hierarchy using these 
new filters, unless you’re absolutely certain of what you’re doing.


# Template Resolution

If the question you want to answer is:

> Which templates WordPress will try to find for this query?

It can be simply answered using the `getTemplates()` method:

```php
global $wp_query; // we will target the main query

$hierarchy = new Brain\Hierarchy\Hierarchy();

var_export( $hierarchy->getTemplates($wp_query) );
```

Assuming same query as above, the output will be:

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

For this example, I will assume that a theme has template files stored in `templates` subfolder and
using `.phtml` as file extension. 

All the code necessary to load those templates according to template hierarchy is the following:

```php
add_action( 'template_redirect', function() {

    $templates = ( new Brain\Hierarchy\Hierarchy() )->getTemplates();
    
    foreach( $templates as $template ) {
      $path = get_template_directory() . "/templates/{$template}.phtml";
      if ( file_exists( $path ) ) {
         require $path;
         exit();
      }
    }
    
} );
```

The example above works, and is just an example of what you can do with this library, however for the
purpose to load templates, this library provides a specific class: `QueryTemplate`.

# Introducing `QueryTemplate`

`QueryTemplate` class makes use of the `Hierarchy` class to get a list of templates to search, then
it looks for those templates and loads the first found.

Example:

```php
add_action( 'template_redirect', function() {

    global $wp_query;
    
    $queryTemplate = new \Brain\Hierarchy\QueryTemplate();
    
    echo $queryTemplate->loadTemplate( $wp_query );
    
    exit();
    
} );
```

What the code above does, is exactly what WordPress does: the proper template is found searching in 
theme folder and in parent theme folder (if current theme is a child theme) then the first template
found is loaded and its content is printed to page.

However, it is **just the default behavior**, and it can be customised.


### Template content is returned

The first thing to note in the last code snippet, is that the template content is **returned** by 
`QueryTemplate::loadTemplate()`.

This is important because without the `echo` what is shown is just a White Screen Of Death.


### Edit template content before to output

Moreover, this feature can be used to alter the content before to output it:

```php
add_action( 'template_redirect', function() {

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate();
        
    // if no WP_Query object is passed to loadTemplate(), the global $wp_query is used
    $content = $queryTemplate->loadTemplate();
    
    echo str_replace( 'example.com', 'new.example.com', $content );
    
    exit();
    
} );
```

The snippet above replaces every occurrence of `example.com` in the page content with `new.example.com`.

If you are thinking this method allows you to use a **template engine** to render templates...
you are thinking well, but there's quite a lot more to know.


## Template Finders

By default, `QueryTemplate` class, searches for templates in theme and parent theme folders.

Just like WordPress does.

However, it is possible to use a different template finder class to do something different.

All template finder classes have to implement the `TemplateFinderInterface` interface.

The library comes with some classes that implement that interface, and of course, it is possible to write a custom one.

Below a list of shipped template finder classes.

#### `FoldersTemplateFinder`
 
The class `FoldersTemplateFinder` can be used to search for templates in some **arbitrary** folders,
instead of theme and parent theme folders.
 
Example:

```php
add_action( 'template_redirect', function() {

    $finder = new \Brain\Hierarchy\Finder\FoldersTemplateFinder([
       __DIR__,
       get_stylesheet_directory(),
       get_template_directory(),
    ]);
    
    $queryTemplate = new \Brain\Hierarchy\QueryTemplate( $finder );  
      
    echo $queryTemplate->loadTemplate();
    
    exit();
    
} );
```

The snippet above will search for templates in the current folder and if template is not found there,
it is searched in theme and parent theme folders.

##### Custom file extensions

`FoldersTemplateFinder` class, by default, searches for files with `.php` extension, but it is possible to
use different file extensions, by passing them as a second constructor argument (either a string or an array of strings):

```php
// This will look for *.phtml files.
$phtml_finder = new \Brain\Hierarchy\Finder\FoldersTemplateFinder(
    [ get_stylesheet_directory(), get_template_directory() ],
    'phtml'
);

// This will look for Twig files first, and fall back to standard PHP files if
// no matching Twig file was found.
$twig_finder = new \Brain\Hierarchy\Finder\FoldersTemplateFinder(
    [ get_stylesheet_directory(), get_template_directory() ],
    [ 'twig', 'php' ]
);
```

Note that custom extensions are case insensitive and that can be passed with or without trailing dot.

#### `SubfolderTemplateFinder`

This template finder class is very similar to `FoldersTemplateFinder`, however it looks for templates
is a specific subfolder of theme (and parent theme) and use theme (and parent theme) folder as fallback:

```php
add_action( 'template_redirect', function() {

    $finder = new \Brain\Hierarchy\Finder\SubfolderTemplateFinder( 'templates' );

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate( $finder );

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

Using code above the templates are searched, in order, in:

 - /path/to/child/theme/templates/
 - /path/to/parent/theme/templates/
 - /path/to/child/theme/
 - /path/to/parent/theme/

`SubfolderTemplateFinder`, just like `FoldersTemplateFinder`, accepts custom file extensions as second
constructor argument.


#### `LocalizedTemplateFinder`

This finder class works in combination with another finder and allows to load templates based on
the current locale:


```php
add_action( 'template_redirect', function() {

    // if no folders provided, theme and parent theme folders are used
    $foldersFinder = new \Brain\Hierarchy\Finder\FoldersTemplateFinder();

    $finder = new \Brain\Hierarchy\Finder\LocalizedTemplateFinder( $foldersFinder );

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate( $finder );

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

Assuming the current locale is `it_IT`, using code above, the templates are searched, in order, in:

 - /path/to/child/theme/it_IT/
 - /path/to/parent/theme/it_IT/
 - /path/to/child/theme/it/
 - /path/to/parent/theme/it/
 - /path/to/child/theme/
 - /path/to/parent/theme/


#### `SymfonyFinderAdapter`

This class allows to use the [Symfony Finder Component](http://symfony.com/doc/current/components/finder.html)
to find templates:

```php
add_action( 'template_redirect', function() {

    $symfonyFinder = new \Symfony\Component\Finder\Finder();
    $symfonyFinder = $symfonyFinder->files()->in( __DIR__ )->name( '*.phtml' );

    $finder = new \Brain\Hierarchy\Finder\SymfonyFinderAdapter( $symfonyFinder );

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate( $finder );

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

#### `CallbackTemplateFinder`

This class can be used to easily integrate 3rd party different loaders with `QueryTemplate` class.

In fact, you need to provide an arbitrary callback that will be called to find templates.

The callback will receive the template name without file extension, e.g. `index` and has to return
the full path of the template if found, or an empty string if the template is not found.

Example:

```php
add_action( 'template_redirect', function() {

    $callback = function( $template ) {
       return realpath(__DIR__ . $template . '.php') ? : '';
    };

    $finder = new \Brain\Hierarchy\Finder\CallbackTemplateFinder( $callback );

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate( $finder );

    echo $queryTemplate->loadTemplate();
    exit();
} );
```

## Core Filters for Template Loading

When WordPress searches for a template in `template-loader.php`, it triggers different filters in the form of
[`{$type}_template`](https://developer.wordpress.org/reference/hooks/type_template/); examples are
 *'single_template'*. *'page_template'* and so on.

Moreover, the found template passes through the [*'template_include'*](https://developer.wordpress.org/reference/hooks/template_include/) filter.

By default, **`QueryTemplate::loadTemplate()` applies same filters**, to maximize compatibility with core
behavior.

This happen no matter the template finder is used.

However, by passing `false` as second argument to the method it will stop to apply those core
filters.


## Introducing Template Loaders

After a template is found with any of the finder classes, `QueryTemplate` has to "load" it.

By default, loading is just a `require` wrapped by `ob_start()` / `ob_get_clean()` so that the template
content is just returned as is.

However, is it possible to *process* the template in some ways, for example, by using a **template engine**.

Custom template loaders have to implement `TemplateLoaderInterface` that has just one method: `load()`,
that receives the full path of the template and have to **return** the template content.

Template loaders can be passed as second constructor argument to `QueryTemplate`.


#### `FileRequireLoader`

This is the unique loader class that ships with the library, and it provides the default behavior.


## Aggregate Loaders

Aggregate loaders uses different "inner" loaders to load templates.

Aggregate loaders have to implement the interface `AggregateTemplateLoaderInterface` that has 2 methods:

- `addLoader(TemplateLoaderInterface $loader, callable $predicate)`
- `addLoaderFactory(callable $loaderFactory, callable $predicate)`

The first is used to add a template loader instance. The second is used to add a factory callback
that once called will return a template loader instance. It is useful when the loader instantiation
is resource expensive to avoid unnecessary instantiation, that is only done if required (_lazy_).

Both methods accept as second argument a "predicate": a callback that will receive the path of the 
template file to load and will return a boolean.

When the predicate returns `true`, the related loader is used to load the template.

#### `CascadeAggregateTemplateLoader`

`CascadeAggregateTemplateLoader` is a simple implementation of an aggregate loader, where the predicates
are evaluated in the same order they are added (FIFO).


#### `ExtensionMapTemplateLoader`

`ExtensionMapTemplateLoader` is another aggregate loader implementation shipped with Hierarchy.

It is used to load different loaders based on template file extension.

It requires an extensions-to-loaders "map" to be passed to constructor.

The map keys are the template file extensions, the values are the loader to be used.

Loaders can be passed as:

- template loader instances
- template loader fully qualified class names
- factory callbacks that once called return template loader instances

The same loader can be used for multiple file extensions, using as map key a string composed by many
file extensions separated by a pipe `|`.

In any case file extensions are case insensitive and can be passed with or without leading dot.

Example:

```php
$loader = new ExtensionMapTemplateLoader([
    'php|phtml' => new FileRequireLoader(),
    'mustache'  => function() { return new MyMustacheAdapter(new Mustache_Engine); },
    'md'        => MyMarkdownRenderer::class
]);
```

After the `ExtensionMapTemplateLoader` is obtained, it is possible to add more loaders using
`addLoader()` and `addLoaderFactory()` methods that are part of the aggregate loader interface.

In this case may comes handy the class `FileExtensionPredicate`, it is an invokable object that once
executed passing a file path to it, return a boolean if file extension is supported. Supported extension(s)
can be configured via constructor. It accepts single extension as string and multiple extensions
as array or pipe-separated string.

Example:

```php
$loader = new ExtensionMapTemplateLoader(['php|phtml' => new FileRequireLoader()]);

$loader->addLoader(
    new MyMarkdownRenderer(),
    new FileExtensionPredicate('md')
);

$loader->addLoaderFactory(
    function() { return new MyMustacheAdapter(new Mustache_Engine); },
    new FileExtensionPredicate(['mustache', 'mustache.html'])
);
```

## `QueryTemplate` Usage Example: Loading and Rendering Mustache Templates

In the following example I will show all the code necessary to find and render [mustache](https://github.com/bobthecow/mustache.php)
templates according to WordPress template hierarchy.

```php
namespace My\Theme;

use Brain\Hierarchy\Finder\SubfolderTemplateFinder;
use Brain\Hierarchy\Loader\TemplateLoaderInterface;
use Mustache_Engine;

class MustacheTemplateLoader implements TemplateLoaderInterface
{

   private $engine;

   public function __construct( Mustache_Engine $engine )
   {
      $this->engine = $engine;
   }

   public function load( $templatePath )
   {
        // let's use a filter to build some context for the template
        $data = apply_filters( 'my_theme_data', ['query' => $GLOBALS['wp_query'], $templatePath );

        $template = file_get_contents( $templatePath );

        return $this->engine->render( $template, $data );
   }
}

add_action( 'template_redirect', function() {

    // will look for "*.mustache" templates in "/templates" subfolder of theme
    $finder = new SubfolderTemplateFinder( 'templates', 'mustache' );

    // make use of the class above
    $loader = new MustacheTemplateLoader( new Mustache_Engine() );

    $queryTemplate = new \Brain\Hierarchy\QueryTemplate( $finder, $loader );

    // 3rd argument of loadTemplate() is passed by reference, and is set to true if template is found
    $found = false;

    // load the rendered template
    $content = $queryTemplate->loadTemplate( $GLOBALS['wp_query'], true, $found );

    // if template was found, let's output it and exit, otherwise WordPress will continue its work
    if ( $found ) {
        echo $content;
        exit();
    }

} );
```

# Requirements

Hierarchy requires **PHP 5.6+** and [Composer](https://getcomposer.org/) to be installed.


# Installation

Best served by Composer, available on Packagist with name [`brain/hierarchy`](https://packagist.org/packages/brain/hierarchy).

# License

Hierarchy is released under MIT.
