<?php


namespace Rest\Models;


use Rest\Core\Model;

class PermissionModel extends Model
{
    public function __construct()
    {
        parent::__construct('permissions');
    }

    function getUserPermissions($userId) {
        $sql = $this->db->prepare("
            SELECT 
                   r.name resource_name,
                   r.id as resource_id,
                   p.permission,
                   p.id as permission_id
            FROM user_permissions up
            JOIN resources r ON r.id = up.resource_id
            JOIN permissions p ON p.id = up.permission_id
            WHERE user_id = :user_id
            UNION
            SELECT r.name resource_name,
                   r.id as resource_id,
                   p.permission,
                   p.id as permission_id
            FROM role_permissions rp
            JOIN user_roles ur on rp.role_id = ur.role_id
            JOIN resources r ON r.id = rp.resource_id
            JOIN permissions p ON p.id = rp.permission_id
            WHERE ur.user_id = :user_id");
        $sql->execute([':user_id' => $userId]);
        $userPermissions = $sql->fetchAll(\PDO::FETCH_ASSOC);
        return $userPermissions;
    }
}
