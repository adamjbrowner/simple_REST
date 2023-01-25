<?php


namespace Rest\Models;


use Rest\Core\Model;

class RoleModel extends Model
{

    public function __construct()
    {
        parent::__construct('roles');
    }

    function getUserRoles($userId) {
        $sql = $this->db->prepare("
            SELECT 
                   r.* 
            FROM roles r
            JOIN user_roles ur on r.id = ur.role_id
            WHERE ur.user_id = :user_id
        ");
        $sql->execute([':user_id' => $userId]);
        return $sql->fetchAll(\PDO::FETCH_ASSOC);
    }

    function deleteUserRole($userId, $roleId) {
        $sql = $this->db->prepare("DELETE FROM user_roles WHERE user_id = :user_id and role_id = :role_id");
        $response = $sql->execute([
            ':user_id' => $userId,
            ':role_id' => $roleId
        ]);
        return $response;
    }

}
