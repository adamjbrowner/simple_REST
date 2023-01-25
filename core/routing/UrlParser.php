<?php


namespace Rest\Core\Routing;


class UrlParser
{
    public static function parseUrl($url) {
        $argKeys = [2, 4, 6];
        $url = parse_url($_SERVER['REQUEST_URI']);
        $urlComps = explode('/', $url['path']);
        $args = [];
        foreach ($urlComps as $key => $comp) {
            if (in_array($key, $argKeys)) {
                array_push($args, $comp);
            }
        }
        return [
            'path' => $url['path'],
            'args' => $args
        ];
    }
}
