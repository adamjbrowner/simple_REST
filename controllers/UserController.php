<?php

namespace Rest\Controllers;

use Rest\Models\ConversationModel;
use Rest\Models\UserModel;
use Rest\Core\Controller;
use Rest\Core\Auth;

class UserController extends Controller
{

    protected $resourceName = 'users';

    protected $validationRules = [
        'first_name' => ['string', 'required'],
        'last_name' => ['string', 'required'],
        'email' => ['email', 'required'],
        'password' => ['string', 'required'],
    ];

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);;
        $this->model = new UserModel();
    }

    function index()
    {
        if ($_GET) {
            $users = $this->model->read($_GET);
        } else {
            $users = $this->model->read();
        }
        foreach ($users as &$user) {
            unset($user['password']);
        }
        return $users;
    }

    public function readById($id)
    {
        $user = $this->model->readById($id);
        unset($user['password']);
        return $user;
    }

    public function update($id)
    {
        $user = $this->model->update($id, $_POST);
        unset($user['password']);
        return $user;
    }

    function updatePassword($id)
    {
        $validation = $this->validator->validateData($_POST, [
            'password' => ['string', 'required'],
            'old_password' => ['string', 'required'],
        ]);
        if ($validation) {
            $user = $this->model->readById($id);
            if (password_verify($_POST['old_password'], $user['password'])) {
                $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
                $user = $this->model->update($id, $data);
                unset($user['password']);
                return $user;
            } else {
                http_response_code(401);
                $this->errorBag->addError('Old password incorrect');
            }
        }
    }


    function create()
    {
        $validation = $this->validator->validateData($_POST, $this->validationRules);
        $post = $_POST;
        if (isset($post['password'])) {
            $post['password'] = password_hash($post['password'], PASSWORD_BCRYPT);
        }
        $result = [];
        if ($validation) {
            $result = $this->model->create($post);
        }
        return $result;
    }
}
