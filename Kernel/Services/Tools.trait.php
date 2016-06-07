<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This trait encapsulates Pandore utilities methods.
 */
trait Tools
{
    /**
     * @brief Convert datetime to timestamp.
     * @param String $date The datetime.
     * @return Int The timestamp.
     */
    public function datetimeToTimestamp($date)
    {
        $hour = substr($date, 11, 2);
        $min = substr($date, 14, 2);
        $sec = substr($date, 17, 2);
        $day = substr($date, 8, 2);
        $month = substr($date, 5, 2);
        $year = substr($date, 0, 4);

        return mktime((int)$hour, (int)$min, (int)$sec, (int)$month, (int)$day, (int)$year);
    }
    
    /**
     * @brief Convert DSN to array.
     * @param String $DSN The DSN.
     * @return ArrayObject The parameters array.
     *
     * @exception Kernel::Exceptions::BadDSNException When the DSN isn't correctly defined.
     *
     * @details
     * A valid DSN has the following format : key1:value1+key2:value2+[...]+keyN:valueN.
     *
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function DSNToArray($DSN)
    {
        // Create the DSN array.
        $DSNArray = new \ArrayObject();
        
        try {
            // Fill the DSN array up.
            $array = explode('+', $DSN);

            foreach($array as $value)
            {
                $exp = explode(':', $value);
                $DSNArray[$exp[0]] = $exp[1];
            }
        } catch(\Exception $e) {
            throw new Exceptions\BadDSNException('The DSN isn\'t correctly defined');
        }
        
        // If the DNS isn't correctly defined.
        if(!($DSNArray->offsetExists('dbms') &&
             $DSNArray->offsetExists('host') &&
             $DSNArray->offsetExists('dbname') &&
             $DSNArray->offsetExists('username') &&
             $DSNArray->offsetExists('password')))
        {
            throw new Exceptions\BadDSNException('The DSN isn\'t correctly defined');
        }
        
        return $DSNArray;
    }
    
    /**
     * @brief Display value in a human readable format.
     * @param Mixed $value The value.
     */
    public function dump($value)
    {
        echo('<pre>'.print_r($value, true).'</pre>');
    }
    
    /**
     * @brief Get the current page name.
     * @return String The current page name.
     */
    public function getCurrentPageName()
    {
        $page = null;

        if(isset($_SERVER['QUERY_STRING']))
        {
            $page = explode('=', $_SERVER['QUERY_STRING']);
            $page = explode('&', $page[1]);
            $page = $page[0];
        }

        return ($page != null && $page != '') ? $page : 'undefined';
    }
    
    /**
     * @brief Get the visitor ip.
     * @return String The visitor ip.
     */
    public function getIp()
    {
        $ip = '';

        if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif(isset($_SERVER['HTTP_CLIENT_IP']))
        {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
    
    /**
     * @brief Get the current datetime.
     * @return String The current datetime.
     */
    public function now()
    {
        return date('Y\-m\-d H\:i\:s');
    }
    
    /**
     * @brief Remove some defined characters from the string.
     * @param String $string The string.
     * @param ArrayObject $characters The characters which have to be removed.
     * @return String The cleaned string.
     */
    public function strclean($string, \ArrayObject $characters)
    {
        $cleaned = '';

        for($i = 0; $i < strlen($string) ; $i++)
        {
            if(!$characters->offsetExists($string[$i]))
            {
                $cleaned .= $string[$i];
            }
        }

        return $cleaned;
    }
    
    /**
     * @brief Cut a string.
     * @param String $string The string.
     * @param Int $start The first char position.
     * @param Int $end The last char position.
     * @return String The cut string.
     */
    public function strcut($string, $start, $end)
    {
        $substring = '';

        for($i = 0 ; $i < strlen($string) ; $i ++)
        {
            if($i >= $start && $i <= $end)
            {
                $substring .= $string[$i];
            }
        }

        return $substring;
    }
    
    /**
     * @brief Makes all characters lowercase except for the first one.
     * @param String $string The string.
     * @return String The formated string.
     */
    public function strformat($string)
    {
        return ucfirst(strtolower($string));
    }
    
    /**
     * @brief Compute a random string.
     * @param Int $size The string size.
     * @return String The string.
     */
    public function strrand($size)
    {
        $str = '';
        $dummy = 'abcdefgh*^%!-@#ijklmnpqrstuvwxy0123456789';
        srand((double)microtime()*1000);
        
        for($i = 0; $i < $size; $i++)
        {
            $str .= $dummy[rand() % strlen($dummy)];
        }
        
        return $str;
    }

    /**
     * @brief Create an uri from parameters.
     * @param String $moduleName The module name.
     * @param String $actionName The action name.
     * @param Array $get The $_GET array.
     * @return String the uri.
     */
    public function uri($moduleName = '', $actionName = '', $get = array())
    {
        // Create the uri.
        $uri = '';

        // If the module name isn't empty.
        if(!empty($moduleName))
        {
            // Add the module name to the uri.
            $uri .= $moduleName.'/';

            // If the action name isn't empty.
            if(!empty($actionName))
            {
                // Add the action name to the uri.
                $uri .= $actionName.'/';

                // If $_GET isn't an array.
                if(!is_array($get))
                {
                    throw new Exceptions\BadTypeException('"Get" parameters must be given as an array.');
                }

                // If $_GET isn't empty.
                if(!empty($get))
                {
                    foreach($get as $key => $value)
                    {
                        // Add each key/value to the uri.
                        $uri .= $key.'/'.$value.'/';
                    }
                }
            }
        }

        return $uri;
    }
     
    /**
     * @brief Compute an absolute url from a relative one.
     * @param String $url The relative url (to www/).
     * @return String The absolute url.
     */
    public function url($url)
    {
        return 'http://'.$_SERVER['HTTP_HOST'].$this->strcut($_SERVER['SCRIPT_NAME'], 0, strripos($_SERVER['SCRIPT_NAME'], '/')).$url;
    }
}

?>