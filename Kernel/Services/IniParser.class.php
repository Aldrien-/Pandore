<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class parses ini files and controls values access.
 */
class IniParser
{
    /**
     * @brief The ini file content.
     * @var ArrayObject.
     */
    private $data;
    
	/**
	 * @brief Constructor.
	 * @param String $iniFilePath The ini file path.
	 *
     * @exception Kernel::Exceptions::FileException When the file is can't be opened.
	 */
	public function __construct($iniFilePath)
	{
        // Parse the ini file.
        $array = parse_ini_file($iniFilePath);
        
        // In case of parse failure.
        if($array === false)
        {
			throw new Exceptions\FileException('File called "'.$iniFilePath.'" can\'t be opened or doesn\'t exist.');
		}
        
        // Set the ini file content.
        $this->data = new \ArrayObject($array);
	}
    
    /**
	 * @brief Get an array from the ini file content.
	 * @param String $name The array name.
	 * @return ArrayObject The values.
     *
     * @exception Kernel::Exceptions::BadKeyException When the associated array doesn't exist in the ini file.
     * @exception Kernel::Exceptions::BadTypeException When the value isn't an array.
	 */
	public function getArray($name)
	{
        // If the key doesn't exists in the ini file.
        if(!$this->data->offsetExists($name))
        {
            throw new Exceptions\BadKeyException('The key "'.$name.'" doesn\'t exist in the ini file.');
        }

        // If the value isn't an array.
        if(!is_array($this->data->offsetGet($name)))
        {
            throw new Exceptions\BadTypeException('The value isn\'t an array : use getValue or getArrayValue.');
        }

        return new \ArrayObject($this->data->offsetGet($name));
    }

    /**
     * @brief Get an array value.
     * @param String $name The array name.
     * @param String $key The value key.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When the key doesn't exist in the array.
     */
    public function getArrayValue($name, $key)
    {
        // Get the array.
        $array = $this->getArray($name);

        // if the key doesn't exist in the array.
        if(!$array->offsetExists($key))
        {
            throw new Exceptions\BadKeyException('The key "'.$key.'" doesn\'t exist in the "'.$name.'" array.');
        }

        return $array->offsetGet($key);
    }

    /**
     * @brief Get a value.
     * @param String $name The value name.
     * @return Mixed The value.
     *
     * @exception Kernel::Exceptions::BadKeyException When key doesn't exist in the ini file.
     * @exception Kernel::Exceptions::BadTypeException When the value is an array.
     */
    public function getValue($name)
    {
        // If the key doesn't exist.
        if(!$this->data->offsetExists($name))
        {
            throw new Exceptions\BadKeyException('The key "'.$name.'" doesn\'t exist in the ini file.');
        }

        // If the value is an array.
        if(is_array($this->data->offsetGet($name)))
        {
            throw new Exceptions\BadTypeException('The value is an array : use getArray or getArrayValue.');
        }

        return $this->data->offsetGet($name);
    }
}

?>