<?php


namespace Rest\Core;


use Rest\Core\Routing\Router;
use Rest\Core\Routing\UrlParser;

class App
{
    private Router $router;
    private Auth $auth;
    private ErrorBag $errorBag;

    public function __construct()
    {
        $routes = require(__DIR__ . '/../config/routes.php');
        $this->router = new Router();
        $this->auth = new Auth();
        $this->errorBag = \Rest\Core\ErrorBag::getInstance();
        $this->router->setRoutes($routes);
    }

    function go() {

        $parsed_url = UrlParser::parseUrl($_SERVER['REQUEST_URI']);

        if (isset($parsed_url['path'])){
            $path = rtrim($parsed_url['path'], "/");
        } else {
            $path = '/';
        }

        $method = $_SERVER['REQUEST_METHOD'];

        $route = $this->router->getRoute($method, $path);
        $response = false;

        if ($route) {
            // TODO try catch, set response code 500
            $response = $this->runRoute($route, $parsed_url['args']);
        } else if ($method !== 'OPTIONS') {
            http_response_code(404);
            $this->errorBag->addError("Route not found");
        }

        $return = [
            'result' => $response,
            'errors' => $this->errorBag->getErrors()
        ];

        header('Content-Type: application/json');
        echo json_encode($return);
    }

    function runRoute($route, $args) {
        $middlewareResponse = true;
        if ($route->middleware) {
            $middlewareIndex = 0;
            while ($middlewareResponse && $middlewareIndex < sizeof($route->middleware)) {
                $middlewareToRun = $route->middleware[$middlewareIndex];
                $middlewareArgs = [];
                if (isset($middlewareToRun['args'])) {
                    foreach($middlewareToRun['args'] as $middlewareArg) {
                        $argIndex = array_search($middlewareArg, $route->arguments);
                        if ($argIndex !== false) {
                            $middlewareArgs[] = $args[$argIndex];
                        } else {
                            $middlewareArgs[] = $middlewareArg;
                        }
                    }
                }
                $middlewareResponse = call_user_func_array([new $middlewareToRun['class']($this->auth), 'run'], $middlewareArgs);
                $middlewareIndex++;
            }
        }
        $response = false;
        if ($middlewareResponse) {
            $response = call_user_func_array([new $route->class($this->auth), $route->function], $args);
        }
        return $response;
    }

}
