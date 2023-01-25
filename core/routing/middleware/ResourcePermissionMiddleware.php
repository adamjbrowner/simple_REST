<?php

namespace Rest\Core\Routing\Middleware;


use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Rest\Core\Auth;

class ResourcePermissionMiddleware implements MiddlewareInterface
{
    private Auth $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    function run(...$args) {
        $resourceName = $args[0];
        $permission = $args[1];

        if (@$_SERVER['HTTP_AUTHORIZATION']) {
            if (!preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
                header('HTTP/1.0 400 Bad Request');
                $this->errorBag->addError("Token not found in request");
            }

            $jwt = $matches[1];
            if (! $jwt) {
                // No token was able to be extracted from the authorization header
                header('HTTP/1.0 400 Bad Request');
                $this->errorBag->addError("Token not found in request");
            }

            $secretKey  = $_ENV['JWT_KEY'];

            try {
                $token = JWT::decode($jwt, $secretKey, ['HS512']);
            } catch(ExpiredException $e) {
                header('HTTP/1.1 401 Unauthorized');
                $this->errorBag->addError("Token invalid");
            }

            $now   = new \DateTimeImmutable();
            $serverName = $_ENV['BASE_URL'];

            if ($token->iss !== $serverName ||
                $token->nbf > $now->getTimestamp() ||
                $token->exp < $now->getTimestamp())
            {
                header('HTTP/1.1 401 Unauthorized');
                $this->errorBag->addError("Token invalid");
            }

        } else {
            header('HTTP/1.1 401 Unauthorized');
            $this->errorBag->addError("Token invalid");
        }


        return $this->auth->validatePermissions($resourceName, $permission);
    }
}
