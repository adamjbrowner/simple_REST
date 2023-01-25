<?php


namespace Rest\Controllers;

use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Models\RoleModel;

class RoleController extends Controller
{
    protected $resourceName = 'resources';

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->model = new RoleModel();
    }

    function getRolesForUser($userId) {
        $roles = $this->model->getUserRoles($userId);
        return $roles;
    }

    function newRoleForUser($userId) {
        $newRole = $this->model->create([
            'user_id' => $userId,
            'role_id' => $_POST['role_id']
        ], 'user_roles');
        return $newRole;
    }

    function deleteUserRole($userId, $roleId) {
        $deletion = $this->model->deleteUserRole($userId, $roleId);
        return $deletion;
    }

}
