<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;
use Kernel\Services as Services;

/**
 * @brief This response encapsulates the rendering (the content) and allows to set http code status.
 *
 * @details
 * Smart variable access :
 * - $this->content : get the content
 * 
 * @see Kernel::Services::IniParser.
 */
class Response
{
    /**
     * @brief The configuration object.
     * @var Kernel::Services::IniParser.
     */
    private $config;
    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    
    /**
     * @brief Constructor.
     * @param Kernel::Services::IniParser $config The configuration.
     */ 
    public function __construct(Services\IniParser $config)
    {
        // Set the main attributes.
        $this->config = $config;

        // Create the inaccessible properties.
        $this->data = new \ArrayObject();

        // Set smart access to data.
        $this->data->offsetSet('content', '');
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
        // If the data doesn't exist.
        if(!$this->data->offsetExists($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }

        return $this->data->offsetGet($key);
    }
    
    /**
     * @brief Write data into inaccessible properties.
     * @param String $key The key.
     * @param Mixed $value The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key isn't settable.
     */
    public function __set($key, $value)
    {
        // If the data doesn't exist.
        if(!$this->data->offsetExists($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" isn\'t settable.');
        }

        $this->data->offsetSet($key, $value);
    }

    /**
     * @brief Send the response.
     */
    public function send()
    {
        echo $this->content;
    }

    /**
     * @brief Set a http response code to the response.
     * @param Int $code The http response code.
     * @param String $message The response message.
     *
     * @details
     * This method throws an exception composed of the http error and possibly a message automatically read from the config.ini.
     * 
     * @exception Kernel::Exceptions::HttpStatusCodeException When a http status code is set.
     */
    public function setHttpStatusCode($code, $message = '')
    {
        throw new Exceptions\HttpStatusCodeException($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$message, $code);
    }
}

?>