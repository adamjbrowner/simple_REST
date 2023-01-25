<?php


namespace Rest\Core\Routing;


class Router
{
    public array $routes = [];

    function setRoutes($routes) {
        $routes = $this->parseRoutes($routes);
        foreach ($routes as $route) {
            $middleware = $route['middleware'] ?? null;
            $this->add($route['method'], $route['url'], $route['function'], $middleware);
        }
    }

    function parseRoutes($routes, $prefix = '') {
        $parsedRoutes = [];
        foreach ($routes as $possibleGroup => $possibleRoutes) {
            if (isset($possibleRoutes['routes'])) {
                if (isset($possibleRoutes['middleware'])) {
                    foreach ($possibleRoutes['routes'] as &$possibleRoute) {
                        $possibleRoute['middleware'] = $possibleRoute['middleware'] ?? [$possibleRoutes['middleware']];
                    }
                }
                $parsedRoutes = array_merge($this->parseRoutes($possibleRoutes['routes'], "$prefix$possibleGroup"), $parsedRoutes);
            } else {
                $possibleRoutes['url'] = "$prefix{$possibleRoutes['url']}";
                $parsedRoutes[] = $possibleRoutes;
            }
        }
        return $parsedRoutes;
    }


    public function add($method, $url, $function, $middleware = null){
        $route = new Route($method, $url, $function, $middleware);
        array_push($this->routes, $route);
    }

    public function addGroup($name, callable $router) {
        $groupedRouter = new Router();
        $router($groupedRouter);
        foreach ($groupedRouter->routes as $groupedRoute) {
            $groupedRoute->parsePath("$name{$groupedRoute->getPath()}");
            array_push($this->routes, $groupedRoute);
        }
    }

    public function getRoute($method, $path) {
        $routeFound = false;
        foreach ($this->routes as $route) {
            if ($route->match($method,$path)) {
                $routeFound = $route;
            }
        }
        return $routeFound;
    }
}
