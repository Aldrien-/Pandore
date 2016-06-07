<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class manages plugins.
 *
 * @details
 * The plugin manager loads plugins from the config file and updates plugins when notifications are received.
 * For convenience, the pre and post dispath notifications are already defined.
 *
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Plugin.
 * @see Kernel::Services::IniParser.
 */
class PluginsManager
{
    /**
     * @brief The kernel plugins namespace.
     * @var String.
     */
    private static $kernelNamespace = 'Kernel\Plugins';
    /**
     * @brief The project plugins namespace.
     * @var String.
     */
    private static $projectNamespace = 'Project\Plugins';

    /**
     * @brief The configuration object.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The plugins container.
     * @var ArrayObject.
     */
    private $plugins;
    /**
     * @brief The request.
     * @var Kernel::Core::Request.
     */
    private $request;
    /**
     * @brief The response.
     * @var Kernel::Core::Response.
     */
    private $response;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration object.
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config)
    {
        // Set main attributes.
        $this->config = $config;
        $this->request = $request;
        $this->response = $response;
        
        // Create the plugin container.
        $this->plugins = new \ArrayObject();
    }

    /**
     * @brief Whether the plugin exists.
     * @param String $pluginName The plugin name.
     * @return Bool Whether the plugin exists.
     */
    public function has($pluginName)
    {
        return $this->plugins->offsetExists($pluginName);
    }

    /**
     * @brief Load plugins from the configuration file.
     */
    public function loadPlugins()
    {
        // Get system plugins.
        try {
            $pluginsName = $this->config->getArray('systemPlugins');
        } catch(\Exception $exception) {
            $pluginsName = new \ArrayObject();
        }

        // If the exceptions handler plugin exists.
        if(in_array('ExceptionsHandler', $pluginsName->getArrayCopy()))
        {
            // Create the exceptions handler plugin class name.
            $pluginClassName = self::$kernelNamespace.'\ExceptionsHandler\ExceptionsHandler';

            // Build the plugin.
            $plugin = Factory\PluginsFactory::get($pluginClassName, $this->request, $this->response, $this->config, $this);

            // Add the plugin to the plugin container.
            $this->plugins->offsetSet('ExceptionsHandler', $plugin);
        }

        // Create a plugin container.
        $plugins = new \ArrayObject();

        foreach($pluginsName as $pluginName)
        {
            // Doesn't consider the exceptions handler plugin.
            if($pluginName == 'ExceptionsHandler')
            {
                continue;
            }

            // Create the plugin class name.
            $pluginClassName = self::$kernelNamespace.'\\'.ucfirst($pluginName).'\\'.ucfirst($pluginName);

            // Build the plugin.
            $plugin = Factory\PluginsFactory::get($pluginClassName, $this->request, $this->response, $this->config, $this);
            
            // Add the plugin to the plugin container.
            $plugins->offsetSet($pluginName, $plugin);
        }

        // Ensures that the exceptions handler plugin is the last added to the plugins container.
        if(in_array('ExceptionsHandler', $pluginsName->getArrayCopy()))
        {
            $plugins->offsetSet('ExceptionsHandler', $this->plugins->offsetGet('ExceptionsHandler'));
        }

        // Set the internal plugin container.
        $this->plugins = $plugins;

        // Get project plugins.
        try {
            $pluginsName = $this->config->getArray('plugins');
        } catch(\Exception $exception) {
            $pluginsName = new \ArrayObject();
        }

        // Create a plugins container.
        $plugins = new \ArrayObject();

        foreach($pluginsName as $pluginName)
        {
            // Create the plugin class name.
            $pluginClassName = self::$projectNamespace.'\\'.ucfirst($pluginName).'\\'.ucfirst($pluginName);

            // Build the plugin.
            $plugin = Factory\PluginsFactory::get($pluginClassName, $this->request, $this->response, $this->config, $this);

            // Add the plugin to the plugins container.
            $plugins->offsetSet($pluginName, $plugin);
        }

        // Ensures that system plugins are the latest added plugins.
        foreach($this->plugins as $pluginName => $plugin)
        {
            if($plugins->offsetExists($pluginName))
            {
                throw new Exceptions\BadKeyException('The plugin "'.$pluginName.'" is already declared as system plugin.');
            }

            $plugins->offsetSet($pluginName, $plugin);
        }

        // Set the final plugins container.
        $this->plugins = $plugins;
    }
    
    /**
     * @brief Notify some plugins.
     * @param String $step The step.
     * @param Array $plugins The notified plugins.
     */
    public function notify($step, $plugins = array())
    {
        // Whether the given plugins array is empty.
        if(empty($plugins))
        {
            // Notify all plugins.
            $iterator = $this->plugins->getIterator();
            while($iterator->valid())
            {
                $iterator->current()->update($step);
                $iterator->next();
            }
        }
        elseif(is_array($plugins))
        {
            // Notify the given plugins.
            $iterator = $this->plugins->getIterator();
            while($iterator->valid())
            {
                if(in_array($iterator->key(), $plugins))
                {
                    $iterator->current()->update($step);
                }
                $iterator->next();
            }
        }
    }

    /**
     * @brief Execute pre dispatch of each plugin.
     */
    public function preDispatch()
    {
        $this->notify('preDispatch');
    }
    
    /**
     * @brief Executes the post dispatch of each plugin.
     */
    public function postDispatch()
    {
        $this->notify('postDispatch');
    }
}

?>