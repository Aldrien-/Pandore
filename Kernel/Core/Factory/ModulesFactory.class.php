<?php

namespace Kernel\Core\Factory;

use Kernel\Core as Core;
use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class implements the factory pattern in order to build modules.
 *
 * @see Kernel::Core::Module.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Services::IniParser.
 */
class ModulesFactory
{
    /**
     * @brief The module class name.
     * @var String.
     */
    private static $moduleClassName = 'Kernel\Core\Module';
    
    /**
     * @brief Build a module from its class name.
     * @param String $className The module class name.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration object.
     * @param Kernel::Core::PluginsManager $pluginsManager The pluginsManager.
     * @return Kernel::Core::Module The module.
     *
     * @exception Kernel::Exceptions::ModulesFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::ModulesFactoryException When the desired class doesn't extend the module class.
     * @exception Kernel::Exceptions::ModulesFactoryException When the module isn't instantiable.
     */

    public static function get($className, Core\Request $request, Core\Response $response, Services\IniParser $config, Core\PluginsManager $pluginsManager)
    {        
        try {
            // Create a reflection class from the module class.
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\ModulesFactoryException($className.' doesn\'t exist.');
        }
        
        // If the class doesn't extend the module class.
        if(!$reflectionClass->isSubclassOf(self::$moduleClassName))
        {
            throw new Exceptions\ModulesFactoryException($className.' doesn\'t extend the module class.');
        }
        
        // If the class isn't instantiable.
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\ModulesFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance($request, $response, $config, $pluginsManager);
    }
}

?>