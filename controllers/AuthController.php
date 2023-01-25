<?php


namespace Rest\Controllers;

use Rest\Core\Controller;
use Rest\Core\Model;
use Rest\Models\UserModel;
use Rest\Models\RoleModel;

class AuthController extends Controller
{

    protected $resourceName = 'auth';

    function login()
    {
        $roleModel = new RoleModel();
        $userModel = new UserModel();

        $email = $_POST['email'];
        $password = $_POST['password'];
        $loggedInUser = $this->auth->login($email, $password);

        if ($loggedInUser) {

            if ($this->auth->userNeedsTwoFactor($loggedInUser['id'])) {
                $userModel->update($loggedInUser['id'], [
                    'two_factor_auth_required' => 1
                ]);
                $this->auth->sendSecondFactorAuthRequest($loggedInUser['id']);
                $this->auth->logUserLogin($loggedInUser['id'], true, true);
                return "Two factor request sent";
            }

            $this->auth->logUserLogin($loggedInUser['id'], true, false);

            $loggedInUser['roles'] = $roleModel->getUserRoles($loggedInUser['id']);
            $token = $this->auth->setToken($loggedInUser);
            $loggedInUser['token'] = $token['token'];
            $loggedInUser['tokenExpiry'] = $token['expiry'];
            return $loggedInUser;
        }

        $userByEmail = $userModel->read(['email' => $email], true);
        if ($userByEmail) {
            $this->auth->logUserLogin($userByEmail['id'], false, false);
        }
        http_response_code(401);
        $this->errorBag->addError("Login failed");
        return false;
    }

    function sendResetPasswordLink()
    {
        $userModel = new UserModel();

        $validation = $this->validator->validateData($_POST, [
            'email' => ['email', 'required'],
        ]);
        if ($validation) {
            $userByEmail = $userModel->read(['email' => $_POST['email']], 1, false, false, true);
            if ($userByEmail) {
                $this->auth->sendResetPasswordLink($userByEmail['id']);
            }
        }
    }

    function resetPasswordFromToken()
    {
        $tokenModel = new Model('reset_password_tokens');
        $userModel = new UserModel();
        $token = $tokenModel->read([
            'token' => $_POST['token']
        ], 1);

        if ($token) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $user = $userModel->update($token['user_id'], $data);
            unset($user['password']);
            $tokenModel->deleteBy(['token' => $token['token']]);
            return $user;
        }
        return false;
    }

    function getCurrentUser()
    {
        $authUser = $this->auth->getUser();
        $user = $this->buildCurrentUser($authUser['id']);
        return $user;
    }

    function verifyOneTimecode()
    {
        $userModel = new UserModel();
        $roleModel = new RoleModel();
        $email = $_POST['email'];
        $userByEmail = $userModel->read(['email' => $email], true);
        if ($userByEmail) {
            unset($userByEmail['password']);
            $code = $_POST['otp'];
            $verified = $this->auth->verifyOneTimecode($userByEmail['id'], $code);
            if ($verified) {
                $userModel->update($userByEmail['id'], [
                    'two_factor_auth_required' => 0
                ]);
                $this->auth->logUserLogin($userByEmail['id'], true, true);
                $userByEmail['roles'] = $roleModel->getUserRoles($userByEmail['id']);
                $token = $this->auth->setToken($userByEmail);
                $userByEmail['token'] = $token['token'];
                $userByEmail['tokenExpiry'] = $token['expiry'];
                return $userByEmail;
            }
        }
    }

    function buildCurrentUser($userId)
    {
        $userModel = new UserModel();
        $user = $userModel->readById($userId);
        $roleModel = new RoleModel();
        $user['roles'] = $roleModel->getUserRoles($userId);
        unset($user['password']);
        return $user;
    }
}
