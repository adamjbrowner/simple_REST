<?php

namespace Rest\Models;

use Rest\Core\Model;

class UserModel extends Model
{
    public function __construct()
    {
        parent::__construct('users');
    }
}
