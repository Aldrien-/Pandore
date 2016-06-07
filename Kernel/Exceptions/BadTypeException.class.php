<?php

namespace Kernel\Exceptions;

/**
 * @brief This exception is thrown when the value hasn't the right type.
 *
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Trait.
 */
class BadTypeException extends \Exception
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