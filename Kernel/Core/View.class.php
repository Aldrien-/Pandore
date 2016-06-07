<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class encapsulates the view elements.
 */
class View
{
    /**
     * @brief The inaccessible properties.
     * @var ArrayObject.
     */
    private $data;
    /**
     * @brief The relative view file path (from the ROOT_PATH).
     * @var String.
     */
    private $name;

    /**
     * @brief Constructor.
     * @param String $name The relative view file path.
     * @param Array $data The view data.
     *
     * @details
     * The view name is a relative path from the ROOT_PATH such as Modules/Foo/Views/Bar.php.
     */
    public function __construct($name = '', $data = array())
    {
        // Set the view name.
        $this->name = $name;

        // Create the inaccessible properties.
        $this->data = new \ArrayObject($data);
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
        if(!$this->has($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist.');
        }

        return $this->data->offsetGet($key);
    }
    
    /**
     * @brief Write data to inaccessible properties.
     * @param String $key The key.
     * @param Mixed $value The value.
     */
    public function __set($key, $value)
    {
        $this->data->offsetSet($key, $value);
    }

    /**
     * @brief Get the view data object.
     * @return ArrayObject The data object.
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @brief Get the view name.
     * @return String The view name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @brief Whether the key exists in inaccessible properties.
     * @param String $key The key.
     * @return Bool Whether the key exists in inaccessible properties.
     */
    public function has($key)
    {
        return $this->data->offsetExists($key);
    }

    /**
     * @brief Whether the view already has a name.
     * @return Bool Whether the view already has a name.
     */
    public function hasName()
    {
        return !empty($this->name);
    }

    /**
     * @brief Merge new data with the existing one.
     * @param ArrayObject $data The data to merge.
     * @param Bool $isReversed Whether the merge must be based one the given data.
     */
    public function mergeData(\ArrayObject $data, $isReversed = false)
    {
        // Whether the merge isn't reversed.
        if(!$isReversed)
        {
            $this->data = new \ArrayObject(array_merge($this->data->getArrayCopy(), $data->getArrayCopy()));
        }
        else
        {
            $this->data = new \ArrayObject(array_merge($data->getArrayCopy(), $this->data->getArrayCopy()));
        }
    }

    /**
     * @brief Set the view data.
     * @param ArrayObject $data The data.
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @brief Set the view name.
     * @param String $name The relative view file path.
     *
     * @details
     * The view name is a relative path from the ROOT_PATH such as Modules/Foo/Views/Bar.php.
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

?>