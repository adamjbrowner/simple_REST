<?php


namespace Rest\Models;


use Rest\Core\Model;

class ModuleModel extends Model
{
    public function __construct()
    {
        parent::__construct('modules');
    }

    function getUserModules($userId) {
        $sql = $this->db->prepare("
        SELECT 
               m.id as module_id,
               m.name as module_name
        FROM modules m
        JOIN user_modules um on m.id = um.module_id
        WHERE um.user_id = :user_id_1
        UNION
        SELECT 
           m.id as module_id,
           m.name as module_name
        FROM modules m
        JOIN role_modules rm on m.id = rm.module_id
        JOIN user_roles ur on rm.role_id = ur.role_id
        WHERE ur.user_id = :user_id_2
        ");
        $sql->execute([':user_id_1' => $userId, ':user_id_2' => $userId]);
        $userModules = $sql->fetchAll(\PDO::FETCH_ASSOC);
        return $userModules;
    }

    function getRoleModules($roleId) {
        $sql = $this->db->prepare("
            SELECT m.* FROM modules m 
            JOIN role_modules rm ON m.id = rm.module_id
            WHERE rm.role_id = :role_id
            ");
        $sql->execute([':role_id' => $roleId]);
        $roleModules = $sql->fetchAll(\PDO::FETCH_ASSOC);
        return $roleModules;
    }

    function deleteRoleModule($roleId, $moduleId) {
        $sql = $this->db->prepare("
        DELETE FROM role_modules
        WHERE module_id = :module_id
        AND role_id = :role_id");
        return $sql->execute([':module_id' => $moduleId, ':role_id' => $roleId]);
    }

}
