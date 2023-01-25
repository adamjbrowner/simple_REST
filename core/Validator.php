<?php



namespace Rest\Core;

function required($value) {
    return preg_match('/\S/', $value);
}

class Validator
{
    private $allRules;
    private ErrorBag $errorBag;

    public function __construct()
    {
        $this->errorBag = ErrorBag::getInstance();
        $this->allRules = [
            'required' => [
                'func' => [$this, 'required']
            ],
            'int' => [
                'func' => 'is_numeric'
            ],
            'string' => [
                'func' => 'is_string'
            ],
            'datetime' => [
                'func' => [$this, 'isDatetime']
            ],
            'email' => [
                'func' => [$this, 'isEmail']
            ]
        ];
    }

    function required($value) {
        return preg_match('/\S/', $value);
    }

    function isEmail($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL);
    }

    function isDatetime($value) {
        return preg_match(
            '/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9])(?:( [0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/',
            $value
        );
    }

    public function validateData($data, $rules)
    {
        $pass = true;
        foreach ($data as $propName => $value) {
            if (isset($rules[$propName])) {
                foreach ($rules[$propName] as $propRule) {
                    if (isset($this->allRules[$propRule])) {
                        if (!call_user_func($this->allRules[$propRule]['func'], $value)) {
                            $pass = false;
                            $this->errorBag->addError("$propName has failed $propRule validation for value '$value'");
                        }
                    } else {
                        $pass = false;
                        $this->errorBag->addError("$propRule validation not found");
                    }
                }
            }
        }

        if (!$pass) {
            header('HTTP/1.1 400 Bad Request');
            return false;
        } else {
            return true;
        }
    }
}


