<?php


namespace Rest\Controllers;


use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Models\ModuleModel;

class ModuleController extends Controller
{

    protected $resourceName = 'modules';

    protected $validationRules = [
        'name' => ['string', 'required'],
    ];

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->model = new ModuleModel();
    }

    function getModulesForUser($userId) {
        $modules = $this->model->getUserModules($userId);
        return $modules;
    }

    function getModulesForRole($roleId) {
        $modules = $this->model->getRoleModules($roleId);
        return $modules;
    }

    function newRoleModule($roleId) {
        $newModule = $this->model->create([
            'role_id' => $roleId,
            'module_id' => $_POST['module_id']
        ], 'role_modules');
        return $newModule;
    }

    function deleteRoleModule($roleId, $moduleId) {
        $deletion = $this->model->deleteRoleModule($roleId, $moduleId);
        return $deletion;
    }
}
