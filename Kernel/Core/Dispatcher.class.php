<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This controller dispatches the request.
 *
 * @details
 * A 404 http code is set to the response in case of an invalid module.
 * A default action is executed whether the module is invalid.
 *
 * @see Kernel::Core::Module.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Factory::ModulesFactory.
 * @see Kernel::Services::IniParser.
 */
class Dispatcher
{
    /**
     * @brief The project modules namespace.
     * @var String.
     */
    private static $projectNamespace = 'Project\Modules';

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
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        // Set the main attributes.
        $this->request = $request;
        $this->response = $response;
        $this->config = $config;
        $this->pluginsManager = $pluginsManager;
    }
    
    /**
     * @brief Dispatch the request.
     */
    public function dispatch()
    {
        // Get the module class name.
        $moduleClassName = self::$projectNamespace.'\\'.ucfirst($this->request->getModuleName()).'\\'.ucfirst($this->request->getModuleName());

        try {
            // Build the module object.
            $module = Factory\ModulesFactory::get($moduleClassName, $this->request, $this->response, $this->config, $this->pluginsManager);
        } catch(Exceptions\ModulesFactoryException $e) {
            // Set a 404 http code to the response.
            $this->response->setHttpStatusCode(404, 'Not found');
        }
        
        // Get the action name.
        $actionName = $this->isActionValid($module, $this->request->getActionName()) ? $this->request->getActionName() : Module::DEFAULT_ACTION_NAME; 
        
        // Execute the module process.
        $module->preExecute();
        $module->execute($actionName);
        $module->postExecute();
        $module->finalize();
    }
    
    /**
     * @brief Whether the module's action is valid.
     * @param Kernel::Core::Module $module The module.
     * @param String $actionName The action name.
     * @return Bool Whether the action is valid.
     */
    private function isActionValid(Module $module, $actionName)
    {
        // Create a reflection class from the module.
        $reflexion = new \ReflectionClass($module);

        // Create the module method name associated with the action.
        $actionNameFormated = Module::formatActionName($actionName);

        return $reflexion->hasMethod($actionNameFormated);
    }
}

?>