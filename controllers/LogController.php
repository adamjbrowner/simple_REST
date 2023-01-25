<?php


namespace Rest\Controllers;


use Rest\Core\Controller;
use Rest\Core\Auth;

class LogController extends Controller
{

    protected $resourceName = 'logs';

    public function __construct(Auth $auth)
    {
        parent::__construct($auth);
    }

    function getErrorLogs() {
        $logs = file_get_contents($_ENV['ERROR_LOG']);
        $logLines = explode("\n[", $logs);

        $errorLog = [];

        foreach ($logLines as $line) {
            $lineSplit = explode('] ', $line);
            if ($lineSplit) {
                $datetime = substr($lineSplit[0], 1);
                $errorMessage = '';
                if (isset($lineSplit[1])) {
                    $errorMessage = json_decode($lineSplit[1])?:$lineSplit[1];
                }
                $error = [
                    'datetime' => $datetime,
                    'error' => $errorMessage
                ];

                if ($errorMessage) {
                    $errorLog[] = $error;
                }
            }
        }

        return $errorLog;
    }

}
