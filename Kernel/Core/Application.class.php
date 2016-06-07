<?php

namespace Kernel\Core;

/**
 * @brief The application initializes and runs the Pandore process.
 *
 * @see Kernel::Core::FrontController.
 */
class Application
{
    /**
     * @brief The configuration file path.
     * @param String.
     */
    private $configFilePath;

    /**
     * @brief The relative path of the configuration folder.
     * @param String.
     */
    private static $configDir = 'Project/Config/';

    /** 
     * @brief Initialize the application.
     */
    public function init()
    {
        // Get the configuration name.
        $configName = file_exists(ROOT_PATH.self::$configDir.$_SERVER['HTTP_HOST'].'.ini') ? $_SERVER['HTTP_HOST'] : 'default';

        // Set the configuration file path.
        $this->configFilePath = ROOT_PATH.self::$configDir.$configName.'.ini';
    }

    /**
     * @brief Run the application.
     */
    public function run()
    {
        // Create the front controller.
        $frontController = new FrontController($this->configFilePath);

        // Dispatch the request.
        $frontController->dispatch();

        // Send the response.
        $frontController->sendResponse();
    }
}

?>