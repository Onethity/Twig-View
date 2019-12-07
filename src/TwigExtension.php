<?php
/**
 * Slim Framework (http://slimframework.com)
 *
 * @link      https://github.com/Onethity/Twig-View
 * @copyright Copyright (c) 2011-2015 Josh Lockhart
 * @license   https://github.com/slimphp/Twig-View/blob/master/LICENSE.md (MIT License)
 */
namespace Slim\Views;

use Slim\Psr7\Uri;
use Slim\Psr7\Factory\UriFactory;

class TwigExtension extends \Twig\Extension\AbstractExtension
{
    /**
     * @var \Slim\Interfaces\RouteParserInterface
     */
    private $routeParser;

    /**
     * @var string|\Psr\Http\Message\UriInterface
     */
    private $uri;

    /**
     * @var string
     */
    private $basePath;

    public function __construct($routeParser, $uri, $basePath)
    {
        $this->routeParser = $routeParser;
        $this->uri = $uri;
        $this->basePath = $basePath;
    }

    public function getName()
    {
        return 'slim';
    }

    public function getFunctions()
    {
        return [
            new \Twig\TwigFunction('path_for', array($this, 'pathFor')),
            new \Twig\TwigFunction('full_url_for', array($this, 'fullUrlFor')),
            new \Twig\TwigFunction('base_url', array($this, 'baseUrl')),
            new \Twig\TwigFunction('is_current_path', array($this, 'isCurrentPath')),
            new \Twig\TwigFunction('current_path', array($this, 'currentPath')),
        ];
    }

    public function pathFor($name, $data = [], $queryParams = [], $appName = 'default')
    {
        return $this->routeParser->urlFor($name, $data, $queryParams);
    }

    /**
     * Similar to pathFor but returns a fully qualified URL
     *
     * @param string $name The name of the route
     * @param array $data Route placeholders
     * @param array $queryParams
     * @param string $appName
     * @return string fully qualified URL
     */
    public function fullUrlFor($name, $data = [], $queryParams = [], $appName = 'default')
    {
        $path = $this->pathFor($name, $data, $queryParams, $appName);

        /** @var Uri $uri */
        if (is_string($this->uri)) {
            $uriFactory = new UriFactory();
            $uri = $uriFactory->createUri($this->uri);
        } else {
            $uri = $this->uri;
        }

        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();

        $host = ($scheme ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '');

        return $host.$path;
    }

    public function baseUrl()
    {
        if (is_string($this->uri)) {
            return $this->uri;
        } else {
            return $this->getBaseUrl($this->uri);
        }
    }

    public function isCurrentPath($name, $data = [])
    {
        return $this->routeParser->urlFor($name, $data) === $this->basePath . '/' . ltrim($this->uri->getPath(), '/');
    }

    /**
     * Returns current path on given URI.
     *
     * @param bool $withQueryString
     * @return string
     */
    public function currentPath($withQueryString = false)
    {
        if (is_string($this->uri)) {
            return $this->uri;
        }

        $path = $this->basePath . '/' . ltrim($this->uri->getPath(), '/');

        if ($withQueryString && '' !== $query = $this->uri->getQuery()) {
            $path .= '?' . $query;
        }

        return $path;
    }

    /**
     * Set the base url
     *
     * @param string|Slim\Psr7\Uri $baseUrl
     * @return void
     */
    public function setBaseUrl($baseUrl)
    {
        $this->uri = $baseUrl;
    }

    /**
     * Return the fully qualified base URL.
     *
     * Note that this method never includes a trailing /
     *
     * This method is not part of PSR-7.
     * 
     * It was a part of Slim 3.
     *
     * @var \Psr\Http\Message\UriInterface
     * @return string
     */
    
    protected function getBaseUrl(\Psr\Http\Message\UriInterface $uri): String
    {
        $scheme = $uri->getScheme();
        $authority = $uri->getAuthority();
        $basePath = $this->basePath;

        if ($authority !== '' && substr($basePath, 0, 1) !== '/') {
            $basePath = $basePath . '/' . $basePath;
        }

        return ($scheme !== '' ? $scheme . ':' : '')
            . ($authority ? '//' . $authority : '')
            . rtrim($basePath, '/');
    }
}
