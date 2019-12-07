# Slim 4 Framework Twig View

This is a [slim/twig-view](https://github.com/slimphp/Twig-View) component, but forked for Slim 4.

## Install

Via [Composer](https://getcomposer.org/)

```bash
$ composer require onethity/slim4-twig-view
```

Requires Slim Framework 4 and PHP 7.1.0 or newer.

## Usage

```php
// Create Container
$container = new Container();

// Set container to create App with on AppFactory
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register Twig View helper
$container->set('view', function ( ) use ($app) {
    $view = new \Slim\Views\Twig(__DIR__ . '/../tpl/', [
        'cache' => false,
    ]);
    
    // Instantiate and add Slim specific extension
    $uriFactory = new \Slim\Psr7\Factory\UriFactory();
    $uri = $uriFactory->createFromGlobals($_SERVER);
    $routeParser = $app->getRouteCollector()->getRouteParser();
    $basePath = $app->getBasePath();
    $view->addExtension(new \Slim\Views\TwigExtension($routeParser, $uri, $basePath));

    return $view;
});

// Define named route
$app->get('/hello/{name}', function ($request, $response, $args) {
    return $this->get('view')->render($response, 'index.html.twig', [
        'name' => $args['name']
    ]);
})->setName('profile');

// Render from string
$app->get('/hi/{name}', function ($request, $response, $args) {
    $str = $this->get('view')->fetchFromString('<p>Hi, my name is {{ name }}.</p>', [
        'name' => $args['name']
    ]);
    $response->getBody()->write($str);
    return $response;
});

// Run app
$app->run();
```


## Custom template functions

`TwigExtension` provides these functions to your Twig templates:

* `path_for()` - returns the URL for a given route.
* `base_url()` - returns the `Uri` object's base URL.
* `is_current_path()` - returns true is the provided route name and parameters are valid for the current path.
* `current_path()` - renders the current path, with or without the query string.


You can use `path_for` to generate complete URLs to any Slim application named route and use `is_current_path` to determine if you need to mark a link as active as shown in this example Twig template:

    {% extends "layout.html" %}

    {% block body %}
    <h1>User List</h1>
    <ul>
        <li><a href="{{ path_for('profile', { 'name': 'josh' }) }}" {% if is_current_path('profile', { 'name': 'josh' }) %}class="active"{% endif %}>Josh</a></li>
        <li><a href="{{ path_for('profile', { 'name': 'andrew' }) }}">Andrew</a></li>
    </ul>
    {% endblock %}


## Credits

- [Josh Lockhart](https://github.com/codeguy)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
