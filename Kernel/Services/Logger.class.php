<?php

namespace Kernel\Services;

/**
 * @brief This class allows write into log file.
 *
 * @details
 * If the file doesn't exist yet, it will be created.
 * A log entry has the following format :
 * MM/DD/YYYY HH:MM:SS  |  Message.
 *
 * @see Kernel::Services::File.
 */
class Logger
{   
    /**
     * @brief Log a message.
     * @param String $logName The log file name. 
     * @param String $message The message.
     */
    public static function log($logName, $message)
    {
        // Get the log file path.
        $path = ROOT_PATH.'Log/'.$logName.'.log';

        // Create the log entry.
        $log = date('m\/d\/Y H\:i\:s  |  ').$message.PHP_EOL.PHP_EOL;
        
        // Create a new writable file.
        $file = new File($path, 'a+');

        // Write the message into the file.
        $file->write($log);
    }
}

?>
