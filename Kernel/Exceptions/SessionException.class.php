<?php

namespace Kernel\Exceptions;

/**
 * @brief This exception is thrown when something bad occurs about session.
 *
 * @see Kernel::Services::Session.
 */
class SessionException extends \Exception
{
    /**
     * @brief Constructor.
     * @param String $message The exception message.
     * @param Int $code The exception code.
     */
    public function __construct($message = null, $code = 0)
    {
        parent::__construct($message, $code);
    }
}

?>