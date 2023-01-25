<?php


namespace Rest\Controllers;

use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Core\Model;

class ResetPasswordTokenController extends Controller
{
    protected $resourceName = 'reset_password_tokens';

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->model = new Model('reset_password_tokens');
    }

    public function getToken()
    {
        if (isset($_GET['token'])) {
            return $this->model->read([
                'token' => $_GET['token']
            ], 1);
        }
        return false;
    }
}
