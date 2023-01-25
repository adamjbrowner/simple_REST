<?php


namespace Rest\Core\Routing\Middleware;

use Rest\Core\Auth;

interface MiddlewareInterface
{
    public function __construct(Auth $auth);

    public function run(...$args);
}
