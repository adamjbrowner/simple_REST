<?php

namespace Rest\Core\Routing\Middleware;

use Rest\Core\Auth;

class AuthRoleGuardMiddleware implements MiddlewareInterface
{

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function run(...$args)
    {
        $rolesNeeded = is_array($args[0]) ? $args[0] : [$args[0]];
        return $this->auth->validateRole($rolesNeeded);
    }
}
