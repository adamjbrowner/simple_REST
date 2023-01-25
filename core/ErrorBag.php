<?php


namespace Rest\Core;


class ErrorBag
{
    private $errors = [];
    private static $instance;

    public static function getInstance() {
        if (self::$instance == null)
        {
            self::$instance = new ErrorBag();
        }
        return self::$instance;
    }

    function addError($errorMessage) {
        $this->errors[] = $errorMessage;
    }

    function getErrors() {
        return $this->errors;
    }
}
