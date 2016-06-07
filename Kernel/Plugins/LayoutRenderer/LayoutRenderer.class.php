<?php

namespace Kernel\Plugins\LayoutRenderer;

use Kernel\Core as Core;

/**
 * @brief This plugin is dedicated to the layout rendering.
 * 
 * @details
 * It's usualy the penultimate plugin in the plugins list but the last executed during the plugins post dispatch operation.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::View.
 * @see Kernel::Core::ViewRenderer.
 */
class LayoutRenderer extends Core\Plugin
{
    /**
     * @brief The layouts path.
     */
    const LAYOUTS_PATH = 'Project/Views/Layouts/';

    /**
     * @brief The layout data.
     * @var stdClass.
     */
    public static $data;
    /**
     * @brief The layout name.
     * @var String.
     */
    public static $layoutName;

    /**
     * @brief Whether the layout rendering is enabled.
     * @var Bool.
     */
    private $isEnabled;
    
    /**
     * @brief Initialize the layout renderer.
     */
    public function init()
    {
        // Create the layout data container.
        self::$data = new \stdClass();

        // Set the layout name.
        self::$layoutName = $this->config->getValue('layout');

        // Enable the layout renderering.
        $this->enable();
    }
    
    /**
     * @brief Disable the layout rendering.
     */
    public function disable()
    {
        $this->isEnabled = false;
    }
    
    /**
     * @brief Enable the layout rendering.
     */
    public function enable()
    {
        $this->isEnabled = true;
    }
    
    /**
     * @brief Execute the layout rendering.
     */
    public function postDispatch()
    {
        // If the layout rendering is enabled.
        if($this->isEnabled)
        {
            // Create a layout view.
            $layout = new Core\View(self::LAYOUTS_PATH.self::$layoutName, get_object_vars(self::$data));

            // Set the response content as the content part of the layout.
            $layout->content = $this->response->content;

            // Create a view renderer in order to render the layout view.
            $viewRenderer = new Core\ViewRenderer($layout, $this->config, $this->pluginsManager);
            
            // Render the layout and makes the result as the new response content.
            $this->response->content = $viewRenderer->render();
        }
    }
}
 
?>