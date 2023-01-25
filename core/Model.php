<?php

namespace Rest\Core;

class Model
{
    protected $db;
    protected $resourceName;
    const RESERVED_WHERE_PARAMS = ["limit" => null, "offset" => null, "order" => null, "include_items" => null];

    public function __construct($resourceName)
    {
        $this->resourceName = $resourceName;
        $this->db = DB::getInstance();
    }

    function create($data, $table = FALSE)
    {
        if ($table == false) {
            $table = $this->resourceName;
        }
        $insertColumns = [];
        $insertValues = [];
        $prepVariables = [];
        foreach ($data as $column => $value) {
            $insertColumns[] = $column;
            $insertValues[] = ":$column";
            $prepVariables[":$column"] = $value;
        }
        $columnString = implode(', ', $insertColumns);
        $valueString = implode(', ', $insertValues);
        $sql = $this->db->prepare("INSERT INTO $table ($columnString) VALUES ($valueString)");
        $sql->execute($prepVariables);
        return $this->readById($this->db->lastInsertId(), $table);
    }

    function addLimitAndOffset($whereString, $limit = false, $offset = false)
    {
        if ($limit) {
            $whereString .= " LIMIT $limit";
            if ($offset) {
                $whereString .= " OFFSET $offset";
            }
        }
        return $whereString;
    }

    function readById($id, $table = false)
    {
        if (!$table) {
            $table = $this->resourceName;
        }
        $sql = $this->db->prepare("SELECT * FROM $table WHERE id = :id");
        $sql->execute([':id' => $id]);
        $result = $sql->fetch(\PDO::FETCH_ASSOC);
        return $result;
    }

    function read($whereData = [], $limit = false, $offset = false, $order = false, $includeItems = true)
    {
        $whereData = array_diff_key($whereData, self::RESERVED_WHERE_PARAMS);
        $where = [];
        $prepVariables = [];
        foreach ($whereData as $column => $value) {
            if ($value == 'null') {
                $where[] = "{$this->resourceName}.$column is null";
            } else {
                $where[] = "{$this->resourceName}.$column = :$column";
                $prepVariables[":$column"] = $value;
            }
        }

        $where = implode(' AND ', $where);
        $sqlString = "SELECT {$this->resourceName}.* FROM {$this->resourceName}";
        if ($where) {
            $sqlString .= " WHERE $where";
        }
        if ($order) {
            $sqlString .= " ORDER BY $order";
        }
        $sqlString = $this->addLimitAndOffset($sqlString, $limit, $offset);

        $sql = $this->db->prepare($sqlString);
        $sql->execute($prepVariables);
        if ($limit == 1) {
            $result = $sql->fetch(\PDO::FETCH_ASSOC);
        } else {
            $result = $sql->fetchAll(\PDO::FETCH_ASSOC);
        }
        if ($includeItems) {
            return $result;
        } else {
            return sizeof($result);
        }
    }

    function update($id, $data)
    {
        $updateColumns = [];
        $prepVariables = [':id' => $id];
        foreach ($data as $column => $value) {
            $updateColumns[] = "$column = :$column";
            $prepVariables[":$column"] = $value;
        }
        $updateString = implode(', ', $updateColumns);
        $sql = $this->db->prepare("UPDATE {$this->resourceName} SET $updateString WHERE id = :id");
        $sql->execute($prepVariables);
        return $this->readById($id);
    }

    function updateBy($whereData, $data)
    {
        $updateColumns = [];
        $prepVariables = [];
        foreach ($data as $column => $value) {
            $updateVar = ":{$column}_update";
            $updateColumns[] = "$column = $updateVar";
            $prepVariables[$updateVar] = $value;
        }
        $updateString = implode(', ', $updateColumns);

        $where = [];
        foreach ($whereData as $column => $value) {
            if ($value == 'null') {
                $where[] = "{$this->resourceName}.$column is null";
            } else {
                $whereVar = ":{$column}_where";
                $where[] = "{$this->resourceName}.$column = $whereVar";
                $prepVariables[$whereVar] = $value;
            }
        }

        $whereString = implode(' AND ', $where);
        $sql = $this->db->prepare("UPDATE {$this->resourceName} SET $updateString WHERE $whereString");
        return $sql->execute($prepVariables);
    }

    function delete($id, $table = false)
    {
        if ($table == false) {
            $table = $this->resourceName;
        }
        $sql = $this->db->prepare("DELETE FROM $table WHERE id = :id");
        $result = $sql->execute([':id' => $id]);
        return $result;
    }

    function deleteBy($whereData, $table = false)
    {
        if ($table == false) {
            $table = $this->resourceName;
        }
        $where = [];
        $prepVariables = [];
        foreach ($whereData as $column => $value) {
            $where[] = "$table.$column = :$column";
            $prepVariables[":$column"] = $value;
        }

        $whereString = implode(' AND ', $where);
        $sql = $this->db->prepare("DELETE FROM $table WHERE $whereString");
        $result = $sql->execute($prepVariables);
        return $result;
    }
}
