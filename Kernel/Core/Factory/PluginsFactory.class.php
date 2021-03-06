<?php

namespace Kernel\Core\Factory;

use Kernel\Core as Core;
use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class implements the factory pattern in order to build plugins.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Services::IniParser.
 */
class PluginsFactory
{
    /**
     * @brief The plugin class name.
     * @var String.
     */
    private static $pluginClassName = 'Kernel\Core\Plugin';
    
    /**
     * @brief Build a plugin from its class name.
     * @param String $className The plugin class name.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration.
     * @param Kernel::Core::PluginsManager $pluginsManager The pluginsManager.
     * @return Kernel::Core::Plugin The plugin.
     *
     * @exception Kernel::Exceptions::PluginsFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::PluginsFactoryException When the desired class doesn't extend the plugin abstract class.
     * @exception Kernel::Exceptions::PluginsFactoryException When the plugin isn't instantiable.
     */
    public static function get($className, Core\Request $request, Core\Response $response, Services\IniParser $config, Core\PluginsManager $pluginsManager)
    {        
        try {
            // Create a reflection class from the plugin class.
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\PluginsFactoryException($className.' doesn\'t exist.');
        }
        
        // If the class doesn't extend the plugin abstract class.
        if(!$reflectionClass->isSubclassOf(self::$pluginClassName))
        {
            throw new Exceptions\PluginsFactoryException($className.' doesn\'t extend the plugin abstract class.');
        }
        
        // If the class isn't instantiable.
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\PluginsFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance($request, $response, $config, $pluginsManager);
    }
    
}

?>