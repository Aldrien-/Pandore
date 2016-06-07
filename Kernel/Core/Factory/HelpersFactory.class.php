<?php

namespace Kernel\Core\Factory;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class implements the factory pattern in order to build helpers.
 *
 * @see Kernel::Core::Helper.
 */
class HelpersFactory
{
    /**
     * @brief The helper class name.
     * @var String.
     */
    private static $helperClassName = 'Kernel\Core\Helper';
    
    /**
     * @brief Build a helper from its class name.
     * @param String $className The helper class name.
     * @return Kernel::Core::Helper The helper.
     *
     * @exception Kernel::Exceptions::HelpersFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::HelpersFactoryException When the desired class doesn't extend the helper abstract class.
     * @exception Kernel::Exceptions::HelpersFactoryException When the helper isn't instantiable.
     */

    public static function get($className)
    {        
        try {
            // Create a reflection class from the helper class.
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\HelpersFactoryException($className.' doesn\'t exist.');
        }

        // If the class doesn't extend the helper abstract class.
        if(!$reflectionClass->isSubclassOf(self::$helperClassName))
        {
            throw new Exceptions\HelpersFactoryException($className.' doesn\'t extends the helper abstract class.');
        }
        
        // If the class isn't instantiable.
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\HelpersFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance();
    }
}

?>