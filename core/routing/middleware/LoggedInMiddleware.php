<?php


namespace Rest\Core\Routing\Middleware;


use Rest\Core\Auth;

class LoggedInMiddleware implements MiddlewareInterface
{

    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function run(...$args)
    {
        return $this->auth->validateLoggedIn();
    }
}
