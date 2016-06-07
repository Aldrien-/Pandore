<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This class allows to render views.
 *
 * @details
 * A view can be composed of subviews which are could be action views or partial views :
 * - A partial view will produce a render which is a view inclusion.
 * - An action view will produce a render which is like the result of an action execution without plugins pre/post dispatch operations.
 *
 * @see Kernel::Core::Dispatcher.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::Request.
 * @see Kernel::Core::Response.
 * @see Kernel::Core::Router.
 * @see Kernel::Core::View.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Tools.
 */
class ViewRenderer
{
    use Services\Tools;

    /**
     * @brief The action views data.
     * @var ArrayObject.
     */
    private $actions;
    /**
     * @brief The configuration object.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The partial views data.
     * @var ArrayObject.
     */
    private $partials;
    /**
     * @brief The plugins manager.
     * @var Kernel::Core::PluginsManager.
     */
    private $pluginsManager;
    /**
     * @brief The view.
     * @var Kernel::Core::View.
     */
    private $view;
    
    /**
     * @brief Constructor.
     * @param Kernel::Core::View $view The view.
     * @param Kernel::Services::IniParser $config The configuration object.
     * @param Kernel::Core::PluginsManager $pluginsManager The plugins manager.
     */
    public function __construct(View $view, Services\IniParser $config, PluginsManager $pluginsManager)
    {
        // Set the main attributes.
        $this->config = $config;
        $this->pluginsManager = $pluginsManager;
        $this->view = $view;

        // Create actions and partials view data.
        $this->actions = new \ArrayObject();
        $this->partials = new \ArrayObject();
    }

    /**
     * @brief Get the instance of the action view data.
     * @param String $name The action view name.
     * @return stdClass The data.
     */
    public function getActionDataInstance($name)
    {
        // If the action view is unknown.
        if(!$this->actions->offsetExists($name))
        {
            // Create an instance for the action view data.
            $action = new \stdClass();
            $action->get = new \stdClass();
            $action->post = new \stdClass();
            $action->cookie = new \stdClass();
            $action->files = new \stdClass();
            $action->server = new \stdClass();

            // Set this instance to its container.
            $this->actions->offsetSet($name, $action);
        }

        return $this->actions->offsetGet($name);
    }

    /**
     * @brief Get the instance of the partial view data.
     * @param String $name The partial view name.
     * @return stdClass The data.
     */
    public function getPartialDataInstance($name)
    {
        // If the partial view is unknown.
        if(!$this->partials->offsetExists($name))
        {
            // Create an instance for the partial view data.
            $partial = new \stdClass();
            
            // Set this instance to its container.
            $this->partials->offsetSet($name, $partial);
        }

        return $this->partials->offsetGet($name);
    }
    
    /**
     * @brief Render the view.
     * @return String The rendered view.
     *
     * @exception Kernel::Exceptions::ViewRendererException When the view file doesn't exist.
     */
    public function render()
    {
        // Get the view name.
        $name = $this->view->getName();

        // If the view file doesn't exist.
        if(!file_exists($this->getFilePath($name)))
        {
            throw new Exceptions\ViewRendererException('The view file called "'.$name.'" doesn\'t exist.');
        }

        // Turn on output buffering
        ob_start();

        try {
            // Include the view file.
            require($this->getFilePath($name));        
        } catch(\Exception $exception) {
            // Clean the output buffer and turn off output buffering
            ob_end_clean();

            throw $exception;
        }

        return ob_get_clean();
    }

    /**
     * @brief Render an action view.
     * @param String $name The action view name.
     * @param String $moduleName The module name.
     * @param String $actionName The action name.
     * @param Array $get The $_GET array.
     * @param Array $post The $_POST array.
     * @param Array $cookie The $_COOKIE array.
     * @param Array $files The $_FILES array.
     * @param Array $server The $_SERVER array.
     *
     * @details
     * The default view data are merged with data defined from the associated module using the partial view name.
     */
    private function action($name, $moduleName, $actionName, $get = array(), $post = array(), $cookie = array(), $files = array(), $server = array())
    {
        // If some action view data exists.
        if($this->actions->offsetExists($name))
        {
            // Merges action view data.
            $get = array_merge($get, get_object_vars($this->actions->offsetGet($name)->get));
            $post = array_merge($post, get_object_vars($this->actions->offsetGet($name)->post));
            $cookie = array_merge($cookie, get_object_vars($this->actions->offsetGet($name)->cookie));
            $files = array_merge($files, get_object_vars($this->actions->offsetGet($name)->files));
            $server = array_merge($server, get_object_vars($this->actions->offsetGet($name)->server));
        }

        // Create the associated uri.
        $uri = $this->uri($moduleName, $actionName, $get);

        // Create a router associated with this uri.
        $router = new Router(array('q' => $uri), $this->config);

        // Create a request object associated with this action.
        $request = new Request($router, $post, $cookie, $files, $server);
        
        // Create a response associated with this action.
        $response = new Response($this->config);

        // Create a dispatcher to dispatch the request.
        $dispatcher = new Dispatcher($request, $response, $this->config, $this->pluginsManager);

        // Dispatch the request in order to produce the action view.
        $dispatcher->dispatch();

        // Output the action view render.
        $response->send();
    }

    /**
     * @brief Get the view file path.
     * @param String $name The view name.
     * @return String The view file path.
     */
    private function getFilePath($name)
    {
        return ROOT_PATH.$name.'.php';
    }

    /**
     * @brief Render a partial view.
     * @param String $name The partial view name.
     * @param String $viewName The associated view name.
     * @param Array $data The view default data.
     *
     * @details
     * The default view data are merged with data defined from the associated module using the partial view name.
     */
    private function partial($name, $viewName, $data = array())
    {
        // Create the partial view.
        $partial = new View($viewName, $data);

        // If some partial view data exists.
        if($this->partials->offsetExists($name))
        {
            // Merge partial view data.
            $partial->mergeData(new \ArrayObject(get_object_vars($this->partials->offsetGet($name))));
        }

        // Keep a reference of the current view.
        $view = &$this->view;

        // Define the current view as the partial one.
        $this->view = $partial;

        // Render the view.
        $return = $this->render();

        // Restore the view reference.
        $this->view = $view;

        // Output the partial view render.
        echo $return;
    }
}

?>