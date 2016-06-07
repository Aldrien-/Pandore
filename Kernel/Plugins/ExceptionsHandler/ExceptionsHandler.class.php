<?php

namespace Kernel\Plugins\ExceptionsHandler;
 
use Kernel\Core as Core;
use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This plugin defines the default exception catching process.
 *
 * @details
 * For http status code exceptions a log can be written and a customizable view is produced if defined.
 * For other exceptions a log can be written and an error 500 and a debug view are produced if wanted.
 *
 * @see Kernel::Core::Plugin.
 * @see Kernel::Core::PluginsManager.
 * @see Kernel::Core::View.
 * @see Kernel::Core::ViewRenderer.
 * @see Kernel::Exceptions::HttpStatusCodeException.
 * @see Kernel::Services::IniParser.
 * @see Kernel::Services::Logger.
 * @see Kernel::Services::Tools.
 */
class ExceptionsHandler extends Core\Plugin
{
    /**
     * @brief The debug view path.
     */
    const DEBUG_VIEW_PATH = 'Kernel/Plugins/ExceptionsHandler/Views/Debug';

    /**
     * @brief The exception.
     * @var Exception.
     */
    public static $exception;

    /**
     * @brief Whether a debug view has to be produced when an exception occurs.
     * @var Bool.
     */
    private $debug;
    /**
     * @brief Whether a log entry has to be written when an exception occurs.
     * @var Bool.
     */
    private $log;
    /**
     * @brief Whether a log entry has to be written when an exception occurs about http status code.
     * @var Bool.
     */
    private $logHttpStatusCode;

    /**
     * @brief Execute the exception catching process.
     */
    public function execute()
    {
        // Whether the exception is about http code.
        if(self::$exception instanceof Exceptions\HttpStatusCodeException)
        {
            // If a http code exception has to produced a log.
            if($this->logHttpStatusCode)
            {
                // Log the exception.
                $this->logException();
            }

            // Set the header. 
            header($_SERVER['SERVER_PROTOCOL'].' '.self::$exception->getCode());

            // Set the response content.
            $this->response->content = self::$exception->getMessage();
        }
        else
        {
            // If an exception has to produced log.
            if($this->log)
            {
                // Log the exception.
                $this->logException();
            }

            // If a debug view has to be produced in case of exceptions.
            if($this->debug)
            {
                // Execute the debug view rendering.
                $this->executeDebug();
            }
        }
    }

    /**
     * @brief Initialize the plugin.
     */
    public function init()
    {
        // Get whether a debug view has to be produced in case of exceptions.
        try {
            $this->debug = (bool) $this->config->getValue('debug');
        } catch(\Exception $exception) {
            $this->debug = false;
        }

        // Get whether a log has to be produced in case of exceptions.
        try {
            $this->log = (bool) $this->config->getValue('log');
        } catch(\Exception $exception) {
            $this->log = true;
        }

        // Get whether a log has to be produced in case of http code exceptions.
        try {
            $this->logHttpStatusCode = (bool) $this->config->getValue('logHttpStatusCode');
        } catch(\Exception $exception) {
            $this->logHttpStatusCode = false;
        }
    }

    /**
     * @brief Execute the debug view rendering.
     */
    private function executeDebug()
    {
        // Set the header. 
        header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error');
        
        // Render the debug view.
        $data = array('exception' => self::$exception);
        $view = new Core\View(self::DEBUG_VIEW_PATH, $data);
        $viewRenderer = new Core\ViewRenderer($view, $this->config, $this->pluginsManager);
        $this->response->content = $viewRenderer->render();
    }

    /**
     * @brief Log an exception message into error.log.
     */
    private function logException()
    {
        Services\Logger::log('error', self::$exception);
    }
}
 
?>