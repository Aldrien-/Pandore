<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class allows to use session in object paradigm with advanced features :
 * - namespace in order to make the session object more structured,
 * - locking in order to protect a part of the session object,
 * - instance control in order to prevent a further access of a part of the session object,
 * - namespaces and variables expiration control in order to limit access of a part of the session object over time or by access count.
 *
 * @details
 * This class uses the original session array and give it the following structure for each namespace : 
 * - data (grouped by key)
 *      - value
 *      - expirationHop
 *      - expirationTimestamp
 * - expirationHop
 * - expirationTimestamp
 * - furtherReachability
 * - locked
 */
class Session
{
    /**
     * @brief The data const.
     */
    const DATA = 'data';
    /**
     * @brief The expiration hop const.
     */
    const EXP_HOP = 'expirationHop';
    /**
     * @brief The expiration timestamp const.
     */
    const EXP_TIME = 'expirationTimestamp';
    /**
     * @brief The further reachability const.
     */
    const REACH = 'furtherReachability';
    /**
     * @brief The locking const.
     */
    const LOCKED = 'locked';
    /**
     * @brief The value const.
     */
    const VALUE = 'value';

    /**
     * @brief Whether the session is further reachable.
     * @var Bool.
     */
    private $isFurtherReachable;
    /**
     * @brief The current session namespace.
     * @var String.
     */
    private $namespace;

    /**
     * @brief Constructor.
     * @param String $namespace The session namespace.
     * @param Bool $isFurtherReachable Whether the session is further reachable.
     */
    public function __construct($namespace, $isFurtherReachable = false)
    {
        // Set the main attributes.
        $this->isFurtherReachable = $isFurtherReachable;
        $this->namespace = $namespace;

        // Start the session process.
        $this->start();
    }

    /**
     * @brief Read a session data from the current namespace.
     * @param String $key The key.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function __get($key)
    {
        // If the key doesn't exist.
        if(!$this->has($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }

        // Check whether the namespace or the variable is expired.
        $this->checkExpiration($key);

        return $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::VALUE);
    }

    /**
     * @brief Write a session value to the current namespace.
     * @param String $key The key.
     * @param Mixed $value The value.
     *
     * @exception Kernel::Exceptions::SessionException When the session is locked.
     */
    public function __set($key, $value)
    {
        // If the session is locked.
        if($this->isLocked())
        {
            throw new Exceptions\SessionException('It\'s not possible to write in a locked session.');
        }

        // Set the value at the right place.
        $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetSet($key, new \ArrayObject(array(
            self::VALUE => $value,
            self::EXP_HOP => null,
            self::EXP_TIME => null,    
        )));
    }

    /**
     * @brief Destroy all session data.
     * @see http://www.php.net/manual/en/function.session-destroy.php.
     */
    public function destroy()
    {
        session_destroy();
    }

    /**
     * @brief Free session variables.
     * @param Bool $all Whether the unset is not limited to the current namespace.
     *
     * @details
     * In case of namespace unset, this method preserves the Pandore session structure.
     * 
     * @see http://www.php.net/manual/en/function.session-unset.php.
     */
    public function free($all = false)
    {
        // Whether the unset is limited to the current namespace.
        if($all === false)
        {
            unset($_SESSION[$this->namespace]);
        }
        else
        {
            session_unset();
        }

        // Restart the session.
        $this->start();
    }

    /**
     * @brief Whether the key exists in session.
     * @return Bool Whether the key exists in session.
     */
    public function has($key)
    {
        return $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetExists($key);
    }

    /**
     * @brief Whether the session is further reachable.
     * @return Bool Whether the session is further reachable.
     */
    public function isFurtherReachable()
    {
        return $this->isFurtherReachable;
    }

    /**
     * @brief Whether the session is locked.
     * @return Bool Whether the session is locked.
     */ 
    public function isLocked()
    {
        return $_SESSION[$this->namespace]->offsetGet(self::LOCKED);
    }

    /**
     * @brief Lock the session.
     */
    public function lock()
    {
        $_SESSION[$this->namespace]->offsetSet(self::LOCKED, true);
    }

    /**
     * @brief Set the access count before expiration.
     * @param Int $hops The access count.
     * @param Mixed $key The variable key.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function setExpirationHops($hops, $key = null)
    {
        // Whether the expiration has to be set for the namespace.
        if($key == null)
        {
            // Set the access count.
            $_SESSION[$this->namespace]->offsetSet(self::EXP_HOP, $hops);
        }
        else
        {
            // If the key doesn't exist.
            if(!$this->has($key))
            {
                throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
            }

            // Set the access count.
            $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetSet(self::EXP_HOP, $hops);
        }
    }

    /**
     * @brief Set the expiration time of a part of the session.
     * @param Int $seconds The time in seconds.
     * @param Mixed $key The key associated with the value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist.
     */
    public function setExpirationSeconds($seconds, $key = null)
    {
        // Compute the timestamp from which the part of the session is consired as expired.
        $timestamp = time() + $seconds;

        // Whether the expiration has to be set for the namespace.
        if($key == null)
        {
            // Set the expiration time.
            $_SESSION[$this->namespace]->offsetSet(self::EXP_TIME, $timestamp);
        }
        else
        {
            // If the key doesn't exist.
            if(!$this->has($key))
            {
                throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
            }

            // Set the expiration time.
            $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetSet(self::EXP_TIME, $timestamp);
        }
    }

    /**
     * @brief Unlock the session.
     */
    public function unlock()
    {
        $_SESSION[$this->namespace]->offsetSet(self::LOCKED, false);
    }

    /**
     * @brief Check whether a part of the session is expired.
     * @param String $key The key of the variable.
     *
     * @exception Kernel::Exceptions::SessionException When the current namespace is expired by access count expiration.
     * @exception Kernel::Exceptions::SessionException When the desired value is expired by access count expiration.
     * @exception Kernel::Exceptions::SessionException When the current namespace is expired by time expiration.
     * @exception Kernel::Exceptions::SessionException When the desired value is expired by time expiration.
     */
    private function checkExpiration($key)
    {
        // Check access count expiration of the namespace.
        if($_SESSION[$this->namespace]->offsetGet(self::EXP_HOP) === 0)
        {
            throw new Exceptions\SessionException('The current namespace "'.$this->namespace.'" is expired because of too much access.');
        }

        // Check access count expiration of the variable.
        if($_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::EXP_HOP) === 0)
        {
            throw new Exceptions\SessionException('The "'.$key.'" variable is expired because of too much access.');
        }

        // Get the current timestamp.
        $timestamp = time();

        // Check time expiration of the namespace.
        if($_SESSION[$this->namespace]->offsetGet(self::EXP_TIME) !== null && $_SESSION[$this->namespace]->offsetGet(self::EXP_TIME) < $timestamp)
        {
            throw new Exceptions\SessionException('The current namespace "'.$this->namespace.'" is expired because of timeout.');
        }

        // Check time expiration of the variable.
        if($_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::EXP_TIME) !== null && $_SESSION[$this->namespace]->offsetGet(self::DATA)->offsetGet($key)->offsetGet(self::EXP_TIME) < $timestamp)
        {
            throw new Exceptions\SessionException('The "'.$key.'" variable is expired because of timeout.');
        }
    }
    
    /**
     * @brief Start the session.
     *
     * @exception Kernel::Exceptions::SessionException When the session start has failed.
     * @exception Kernel::Exceptions::SessionException When the current part of the session was made unreachable. 
     */
    private function start()
    {
        // If the session isn't started yet.
        if(session_id() == '')
        {
            // Set the session name.
            session_name($_SERVER['HTTP_HOST']);

            // If the session start has failed.
            if(session_start() === false)
            {
                throw new Exceptions\SessionException('Session start has failed.');
            }
        }

        // If the namespace doesn't exist in the session yet.
        if(!array_key_exists($this->namespace, $_SESSION))
        {
            // Create the namespace in the session.
            $_SESSION[$this->namespace] = new \ArrayObject();
            $_SESSION[$this->namespace]->offsetSet(self::DATA, new \ArrayObject());
            $_SESSION[$this->namespace]->offsetSet(self::EXP_HOP, null);
            $_SESSION[$this->namespace]->offsetSet(self::EXP_TIME, null);
            $_SESSION[$this->namespace]->offsetSet(self::REACH, $this->isFurtherReachable);
            $_SESSION[$this->namespace]->offsetSet(self::LOCKED, false);
        }
        else
        {
            // If the current namespace can't be reach anymore.
            if($_SESSION[$this->namespace]->offsetGet(self::REACH) === true)
            {
                throw new Exceptions\SessionException('The current namespace can\'t be reach anymore.');
            }
        }
    }
}

?>