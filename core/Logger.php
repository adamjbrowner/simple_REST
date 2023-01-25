<?php


namespace Rest\Core;


class Logger
{

    function logEvent($eventDescription, $userId = null) {
        if (!file_exists($_ENV['EVENT_LOG'])) {
            touch($_ENV['EVENT_LOG']);
        }
        $events = file_get_contents($_ENV['EVENT_LOG']);
        $eventsJson = json_decode($events);
        $eventsJson[] = [
            'event' => $eventDescription,
            'user_id' => $userId,
            'timestamp' => time()
        ];
        $eventLog = fopen($_ENV['EVENT_LOG'], 'w');
        $eventJson = json_encode($eventsJson);
        fwrite($eventLog, $eventJson);
    }

    function logError($errno, $errstr, $errfile, $errline) {
        $error = [
            'error_no' => $errno,
            'error_str' => $errstr,
            'error_file' => $errfile,
            'error_line' => $errline,
        ];
        error_log(json_encode($error));
    }
}
