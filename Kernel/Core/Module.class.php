<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Plugins as Plugins;
use Kernel\Services as Services;

/**
 * @brief This controller defines minimal requirements and the execution process of actions.
 *
 * @details
 * The execution process is composed of the following steps :
 * - init which is associated with the constructor,
 * - preExecute which is executed before the action,
 * - postExecute which is executed after the action,
 * - finalize which is execute at the end of the execution process.
 * These methods allow to customized the execution process of each module.
 *
 * Smart access of some variables :
 * - $this->config : get the configuration object
 * - $this->layout : get the layout (the main view)
 * - $this->pluginsManager : get the plugins manager
 * - $this->request : get the request
 * - $this->response : get the response
 * - $this->view : get the view associated with the current action
 *
 * @see Kernel::Core::Factory::HelpersFactory.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::View.
 * @see Kernel::Core::ViewRenderer.
 * @see Kernel::Plugins::LayoutRenderer.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Tools.
 */
abstract class Module
{
    use Services\Tools;

    /**
     * @brief The default action name.
     */
    const DEFAULT_ACTION_NAME = 'default';
    
    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    /**
     * @brief The module helpers.
     * @var ArrayObject.
     */
    private $helpers;
    /**
     * @brief Whether the current execution process must produces action rendering.
     * @var Bool.
     */
    private $mustRender;
    /**
     * @brief The view renderer.
     * @var Kernel::Core::ViewRenderer.
     */
    private $viewRenderer;

    /**
     * @brief Constructor.
     * @param Kernel::Core::Request $request The request.
     * @param Kernel::Core::Response $response The response.
     * @param Kernel::Services::IniParser $config The configuration object.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(Request $request, Response $response, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        // Create the inaccessible properties.
        $this->data = new \ArrayObject();

        // Set smart access to data.
        $this->data->offsetSet('config', $config);
        $this->data->offsetSet('pluginsManager', $pluginsManager);
        $this->data->offsetSet('request', $request);
        $this->data->offsetSet('response', $response);
        $this->data->offsetSet('view', new View());

        // If the layout renderer plugin exist.
        if($this->pluginsManager->has('LayoutRenderer'))
        {
            // Set the layout as a smart access.
            $this->data->offsetSet('layout', Plugins\LayoutRenderer\LayoutRenderer::$data);
        }

        // Create the helpers container.
        $this->helpers = new \ArrayObject();

        // By default, module actions must render.
        $this->mustRender = true;

        // Create the view renderer.
        $this->viewRenderer = new ViewRenderer($this->view, $this->config, $this->pluginsManager);
        
        // Initialize the module.
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
     * @brief Execute the action.
     * @param String $actionName The action name.
     */
    public function execute($actionName)
    {
        // Whether the view isn't defined yet.
        if(!$this->view->hasName())
        {
            // Set the view name from the action name.
            $this->setViewName($actionName);
        }

        // Create the module method name associated with the action.
        $action = Module::formatActionName($actionName);
        
        // Execute the action method.
        $this->$action();
    }
    
    /**
     * @brief Finalize the execution process.
     * @details This method is called at the end of the execution process.
     */
    public function finalize()
    {
        // If the action must render.
        if($this->mustRender)
        {
            // Render the view and add the result to the response content.
            $this->response->content = $this->viewRenderer->render();
        }
    }
    
    /**
     * @brief Initialize the module.
     * @details This method is called into the constructor.
     */
    public function init() {}
    
    /**
     * @brief The post-execution method.
     * @details This method is called after the action execution.
     */
    public function postExecute() {}
    
    /**
     * @brief The pre-execution method.
     * @details This method is called before the action execution.
     */
    public function preExecute() {}
    
    /**
     * @brief Format the action name.
     * @param String $actionName The action name.
     * @return String The formated action name.
     */
    public static function formatActionName($actionName)
    {
        return ucfirst($actionName).'Action';
    }

    /**
     * @brief Get action view data.
     * @param String $name The action view name.
     * @return stdClass The action view data.
     *
     * @details
     * This method allows to modify get, post, cookie, files and server arrays of action views declared in view files.
     * 
     * Use :
     * - $this->actionData('NAME')->get->foo = bar;
     * - $this->actionData('NAME')->post->foo = bar;
     * - $this->actionData('NAME')->cookie->foo = bar;
     * - $this->actionData('NAME')->files->foo = bar;
     * - $this->actionData('NAME')->server->foo = bar;
     */
    protected function actionData($name)
    {
        return $this->viewRenderer->getActionDataInstance($name);
    }

    /**
     * @brief The default action.
     * 
     * @details
     * By default, the default action is a 404.
     */
    protected function defaultAction()
    {
        $this->response->setHttpStatusCode(404, 'Not found');
    }

    /**
     * @brief Disable the action rendering.
     */
    protected function disableActionRendering()
    {
        $this->mustRender = false;
    }
    
    /**
     * @brief Disable the layout rendering.
     */
    protected function disableLayoutRendering()
    {
        $this->notify('disable', array('LayoutRenderer'));
    }

    /**
     * @brief Get dynamically an instance of the helper.
     * @param String $name The helper name.
     * @return Mixed The helper instance.
     */
    protected function helper($name)
    {
        // If the helper instance doesn't exist yet.
        if(!$this->helpers->offsetExists(ucfirst($name)))
        {
            // Get the helper class name.
            $helpClassName = substr(get_class($this), 0, strripos(get_class($this), '\\')).'\\'.'Helpers'.'\\'.ucfirst($name);

            // Build the helper.
            $helper = Factory\HelpersFactory::get($helpClassName);

            // Set the instance to the helpers container.
            $this->helpers->offsetSet(ucfirst($name), $helper);
        }
        
        return $this->helpers->offsetGet(ucfirst($name));
    }

    /**
     * @brief Get partial view data.
     * @param String $name The partial view name.
     * @return stdClass The partial view data.
     *
     * @details
     * This method allows to modify data of partial views declared in view files.
     * 
     * Use :
     * - $this->partialData('NAME')->foo = bar;
     */
    protected function partialData($name)
    {
        return $this->viewRenderer->getPartialDataInstance($name);
    }
    
    /**
     * @brief Notify plugins.
     * @param String $step The plugins step.
     * @param Array $plugins The affected plugins.
     */
    protected function notify($step, $plugins = array())
    {
        $this->pluginsManager->notify($step, $plugins);
    }

    /**
     * @brief Set the layout name.
     * @param String $name The layout name.
     */
    protected function setLayoutName($name)
    {
        // If the layout renderer exist.
        if($this->pluginsManager->has('LayoutRenderer'))
        {
            // Set the layout name to the layout renderer.
            Plugins\LayoutRenderer\LayoutRenderer::$layoutName = $name;
        }
    }

    /**
     * @brief Set the action view name.
     * @param String $name The name.
     */
    protected function setViewName($name)
    {
        $this->view->setName('Project/Modules/'.substr(get_class($this), strripos(get_class($this), '\\') + 1).'/Views/'.ucfirst($name));
    }
}

?>