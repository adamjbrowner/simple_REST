<?php


namespace Rest\Controllers;


use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Models\PermissionsModel;

class PermissionController extends Controller
{

    protected $resourceName = 'permissions';

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->model = new PermissionsModel();
    }

    function getPermissionsForUser($userId) {
        $permissions = $this->model->getUserPermissions($userId);
        return $permissions;
    }
}
