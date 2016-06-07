<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Plugins as Plugins;
use Kernel\Services as Services;

/**
 * @brief The front controller handles the request.
 * 
 * @details
 * First of all, the front controller loads the project configuration and builds main objects such as the router, the request, the response and the plugins manager.
 * Then, the front controller dispatches the request using the dispatcher and the pre/post dispatch calls of plugins. If something bad occurs, the exceptions handler plugin is called and has to handle the response.
 * Finally, the front controller sends the response.
 *
 * @see Kernel::Core::DataSourceProxy.
 * @see Kernel::Core::Dispatcher.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Router.
 * @see Kernel::Plugins::ExceptionsHandler.
 * @see Kernel::Services::IniParser.
 */
class FrontController
{
    /**
     * @brief The configuration object.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The plugins manager.
     * @var Kernel::Core::PluginsManager.
     */
    private $pluginsManager;
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
     * @param String $configFilePath The configuration file path.
     */ 
    public function __construct($configFilePath)
    {
        // Create the configuration object.
        $this->config = new Services\IniParser($configFilePath);
        
        // Create the router.
        $router = new Router($_GET, $this->config);
        
        // Create the request object.
        $this->request = new Request($router, $_POST, $_COOKIE, $_FILES, $_SERVER);
        
        // Create the response object.
        $this->response = new Response($this->config);

        // Create the plugin manager.
        $this->pluginsManager = new PluginsManager($this->request, $this->response, $this->config);
    }
    
    /**
     * @brief Dispatch the request.
     */
    public function dispatch()
    {
        try {
            // Load plugins.
            $this->pluginsManager->loadPlugins();

            // If the module name doesn't exist.
            if($this->request->getModuleName() === '')
            {
                throw new Exceptions\BadKeyException('The key "module" doesn\'t exist in the configuration file or isn\'t valid.');
            }

            // Get the sources array.
            try {
                $sources = $this->config->getArray('sources');
            } catch(\Exception $exception) {
                $sources = new \ArrayObject();
            }

            // Get the data sources' names array.
            try {
                $DSNs = $this->config->getArray('dsns');
            } catch(\Exception $exception) {
                $DSNs = new \ArrayObject();
            }

            // Initialize the data source proxy.
            DataSourceProxy::init($sources, $DSNs);

            // Pre-dispatch plugins.
            $this->pluginsManager->preDispatch();
        
            // Create the dispatcher.
            $dispatcher = new Dispatcher($this->request, $this->response, $this->config, $this->pluginsManager);

            // Dispatch the request.
            $dispatcher->dispatch();

            // Post-dispatch plugins.
            $this->pluginsManager->postDispatch();
        } catch(\Exception $exception) {
            // Whether the exception handler plugin exists.
            if($this->pluginsManager->has('ExceptionsHandler'))
            {
                // Set the exception.
                Plugins\ExceptionsHandler\ExceptionsHandler::$exception = $exception;

                // Execute the exception catching process.
                $this->pluginsManager->notify('execute', array('ExceptionsHandler'));
            }
            else
            {
                throw $exception;
            }
        }
    }

    /**
     * @brief Send main response.
     */
    public function sendResponse()
    {
        // Send the response.
        $this->response->send();
    }
}

?>