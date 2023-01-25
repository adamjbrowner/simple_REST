<?php


namespace Rest\Controllers;

use Rest\Core\Controller;
use Rest\Core\Auth;
use Rest\Models\NotificationModel;

class NotificationController extends Controller
{
    protected $resourceName = 'notifications';

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
        $this->model = new NotificationModel();
    }

    function getForUser($userId) {
        $where = $_GET;
        $where['user_id'] = $userId;
        return $this->model->read($where);
    }
}
