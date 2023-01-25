<?php


namespace Rest\Controllers;


use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Core\DB;

class SystemStatusController extends Controller
{

    protected $resourceName = 'system-status';

    function index()
    {
        $db = new DB();
        $status = $db->getAttribute(\PDO::ATTR_CONNECTION_STATUS);

        return [
            'api' => ['status' => true, 'text' => 'open'],
            'db' => ['status' => true, 'text' => $status]
        ];
    }
}
