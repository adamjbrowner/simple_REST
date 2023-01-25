<?php


namespace Rest\Models;


use Rest\Core\Model;

class AuthLogModel extends Model
{
    public function __construct()
    {
        parent::__construct('auth_log');
    }
}
