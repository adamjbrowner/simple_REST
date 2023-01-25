<?php


namespace Rest\Core\Routing;


class Route
{
    public $class;
    public $function;
    public $method;
    public $expression;
    public $arguments;
    public $path;
    public $middleware = null;

    public function __construct($method, $path, $handler, $middleware = null)
    {
        list($this->class, $this->function) = explode("/", $handler, 2);
        $this->middleware = $middleware;
        $this->method = $method;
        $this->parsePath($path);
    }

    public function parsePath($path) {
        $this->setArguments($path);
        $exp = preg_replace("|{\w*}|", '\d*', $path);
        $exp = str_replace('/', '\/', $exp);
        $this->expression = $exp;
        $this->path = $path;
    }

    function setArguments($path) {
        preg_match_all('|\{(.*?)\}|', $path, $matches);
        if ($matches) {
            $this->arguments = $matches[1];
        }
    }

    public function getPath() {
        return $this->path;
    }

    public function match($method, $url) {
        return $this->method === $method && preg_match("|^$this->expression$|", $url);
    }
}
