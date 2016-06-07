<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This abstract class defines the minimal requirements for each plugin object.
 *
 * @details
 * Plugins follow the observer pattern using their update method.
 * Plugins are essential elements of the request dispatch with their pre and post dispatch methods called through the update one.
 *
 * Smart variables access :
 * - $this->config : get the configuration object
 * - $this->pluginsManager : get the plugins manager
 * - $this->request : get the request
 * - $this->response : get the response
 *
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Tools.
 */
abstract class Plugin
{
    use Services\Tools;

    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration object.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        // Create the inaccessible data.
        $this->data = new \ArrayObject();

        // Set smart access to data.
        $this->data->offsetSet('config', $config);
        $this->data->offsetSet('pluginsManager', $pluginsManager);
        $this->data->offsetSet('request', $request);
        $this->data->offsetSet('response', $response);
        
        // Initialize the plugin.
        $this->init();
    }

    /**
     * @brief Read data from inaccessible properties.
     * @param String $key The key.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function __get($key)
    {
        // If the key doesn't exist.
        if(!array_key_exists($key, $this->data))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }

        return $this->data[$key];
    }
    
    /**
     * @brief Execute the step. 
     * @param String $step The step in other words the plugin method name.
     */
    public function update($step)
    {
        // Whether the method exists.
        if($this->isMethodExist($step))
        {
            // Executes the method.
            $this->$step();
        }
    }

    /**
     * @brief Initialize plugin.
     */
    protected function init() {}

    /**
     * @brief Notify the given plugins plugins.
     * @param String $step The plugins step.
     * @param Array $plugins The plugins.
     */
    protected function notify($step, $plugins = array())
    {
        $this->pluginsManager->notify($step, $plugins);
    }
    
    /**
     * @brief Whether the method exists.
     * @param String $methodName The method name.
     * @return Bool Whether the method exists.
     */
    private function isMethodExist($methodName)
    {
        // Create a reflexion class from the plugin instance.
        $reflexion = new \ReflectionClass($this);
        
        return $reflexion->hasMethod($methodName);
    }
}

?>