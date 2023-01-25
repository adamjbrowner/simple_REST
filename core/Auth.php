<?php

namespace Rest\Core;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

use Rest\Models\AuthLogModel;
use Rest\Models\PermissionModel;
use Rest\Models\UserModel;
use Twilio\Rest\Client;
use Rest\Core\EmailClient;

class Auth
{
    private ErrorBag $errorBag;
    private $user = NULL;
    private $emailClient;

    public function __construct()
    {
        $this->errorBag = ErrorBag::getInstance();
        $this->emailClient = new EmailClient();
        $this->setUser();
    }

    function login($email, $password)
    {
        $userModel = new UserModel();
        $users = $userModel->read([
            'email' => $email
        ]);
        $matchedUser = false;
        foreach ($users as $user) {
            if (password_verify($password, $user['password'])) {
                $matchedUser = $user;
                break;
            }
        }
        if ($matchedUser) {
            unset($matchedUser['password']);
            $this->user = $matchedUser;
        }
        return $matchedUser;
    }

    function getUser()
    {
        return $this->user;
    }

    function userHasRoles($rolesRequired): bool
    {
        if (!is_array($rolesRequired)) {
            $rolesRequired = [$rolesRequired];
        }
        return $this->user && array_intersect(array_column($this->user['roles'], 'name'), $rolesRequired);
    }

    function setToken($user)
    {
        $secretKey  = $_ENV['JWT_KEY'];
        $tokenId    = base64_encode(random_bytes(16));
        $issuedAt   = new \DateTimeImmutable();
        $expire     = $issuedAt->modify('+6 hours')->getTimestamp();
        $serverName = $_ENV['BASE_URL'];

        $data = [
            'iat'  => $issuedAt->getTimestamp(),
            'jti'  => $tokenId,
            'iss'  => $serverName,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire,
            'data' => [
                'user' => $user,
            ]
        ];

        $token['token'] = JWT::encode(
            $data,
            $secretKey,
            'HS512'
        );
        $token['expiry'] = $expire;
        return $token;
    }

    function setUser()
    {
        $user = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            $jwt = $matches[1];
            if ($jwt) {
                $secretKey  = $_ENV['JWT_KEY'];
                $token = false;
                try {
                    $token = JWT::decode($jwt, $secretKey, ['HS512']);
                } catch (ExpiredException $e) {
                }
                if ($token) {
                    $now   = new \DateTimeImmutable();
                    $serverName = $_ENV['BASE_URL'];

                    if (
                        $token->iss == $serverName &&
                        $token->nbf <= $now->getTimestamp() &&
                        $token->exp >= $now->getTimestamp()
                    ) {
                        $user = (array) $token->data->user;
                    }
                }
            }
        }
        $this->user = $user;
    }

    function validatePermissions($resourceName, $permission): bool
    {
        $permissionsModel = new PermissionModel();
        $userPermissions = $permissionsModel->getUserPermissions($this->user['id']);
        $validUser = false;
        foreach ($userPermissions as $userPermission) {
            if ($userPermission['resource_name'] == $resourceName && $userPermission['permission'] == $permission) {
                $validUser = true;
                break;
            }
        }
        if (!$validUser) {
            http_response_code(403);
            $this->errorBag->addError("You are not authorized to access that resource");
            return false;
        }
        return true;
    }

    function validateRole($rolesNeeded): bool
    {
        $allowed = $this->userHasRoles($rolesNeeded);
        if (!$allowed) {
            http_response_code(403);
            $this->errorBag->addError("You are not authorized to access that route");
        }
        return $allowed;
    }

    function validateLoggedIn()
    {
        if (!$this->user) {
            http_response_code(401);
            $this->errorBag->addError("You must be logged in to access this resource");
            return false;
        }
        return true;
    }

    function setAuthError($message = "You are not authorized to access that resource")
    {
        http_response_code(403);
        $this->errorBag->addError($message);
    }

    function userNeedsTwoFactor($userId)
    {
        $authLogModel = new AuthLogModel();
        $userModel = new UserModel();

        $user = $userModel->readById($userId);

        $needsTwoFactor = boolval($user['two_factor_auth_required']);

        $userRecords = $authLogModel->read(['user_id' => $userId]);

        if ($userRecords) {
            usort($userRecords, function ($a, $b) {
                return $b['login_attempt_made_at'] <=> $a['login_attempt_made_at'];
            });

            $latestLogin = $userRecords[0];
            $ip = $_SERVER['REMOTE_ADDR'];
            $agent = $_SERVER['HTTP_USER_AGENT'];
            if ($ip !== $latestLogin['user_ip'] || $agent !== $latestLogin['user_agent_name']) {
                $needsTwoFactor = true;
            }
        }

        return $needsTwoFactor && $_ENV['TWO_FACTOR_ENABLED'] === true;
    }

    function logUserLogin($userId, $loginSuccess, $secondFactorInit)
    {
        $authLogModel = new AuthLogModel();
        $data = [
            'user_id' => $userId,
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent_name' => $_SERVER['HTTP_USER_AGENT'],
            'login_success' => intval($loginSuccess),
            'second_factor_initiated' => intval($secondFactorInit)
        ];
        $authLogModel->create($data);
    }

    private function generateOtp($userId)
    {
        $otp = rand(100000, 999999);

        $otpModel = new Model('one_time_codes');
        $otpModel->create([
            'code' => $otp,
            'user_id' => $userId
        ]);

        return $otp;
    }

    function sendSecondFactorAuthRequest($userId)
    {
        $userModel = new UserModel();
        $user = $userModel->readById($userId);
        $otp = $this->generateOtp($user['id']);

        $message = "Please enter code $otp";

        if ($user) {
            switch ($user['two_factor_auth_preference']) {
                case 'sms':
                    $basic  = new \Vonage\Client\Credentials\Basic($_ENV['VONAGE_API_KEY'], $_ENV['VONAGE_API_SECRET']);
                    $client = new \Vonage\Client($basic);

                    $response = $client->sms()->send(
                        new \Vonage\SMS\Message\SMS($user['phone_number'], "REST", $message)
                    );

                    $message = $response->current();

                    return $message->getStatus() == 0;
                    break;
                case 'email':
                    $this->emailClient->send($user['email'], $user['first_name'], "One-time-password", $message);
                    break;
            }
        } else {
            http_response_code(404);
        }
    }

    function generatePasswordResetToken($userId)
    {
        $tokenModel = new Model('reset_password_tokens');
        $token = bin2hex(openssl_random_pseudo_bytes(20));
        $tokenModel->create([
            'user_id' => $userId,
            'token' => $token
        ]);
        return $token;
    }

    function sendResetPasswordLink($userId)
    {
        $userModel = new UserModel();
        $user = $userModel->readById($userId);
        $token = $this->generatePasswordResetToken($userId);
        $link = "{$_ENV['APP_URL']}/reset-password/$token";
        $this->emailClient->send($user['email'], $user['first_name'], "Password Reset", $link);
    }

    function verifyOneTimecode($userId, $otp)
    {
        $otpModel = new Model('one_time_codes');
        $where = [
            'code' => $otp,
            'user_id' => $userId
        ];
        $code = $otpModel->read($where);
        if ($code) {
            $otpModel->deleteBy($where);
            return true;
        }
        http_response_code(401);
        $this->errorBag->addError("OTP verification failed");
        return false;
    }

    private function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min;
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1;
        $bits = (int) $log + 1;
        $filter = (int) (1 << $bits) - 1;
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter;
        } while ($rnd > $range);
        return $min + $rnd;
    }

    private function getToken($length)
    {
        $token = "";
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
        $codeAlphabet .= "0123456789";
        $max = strlen($codeAlphabet);

        for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max - 1)];
        }

        return $token;
    }
}
