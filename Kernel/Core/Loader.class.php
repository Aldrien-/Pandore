<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;

/**
 * @brief The loader automatically includes files using namespaces.
 *
 * @details
 * All files aren't automatically included but loader can dynamically include :
 * - Class files named ClassName.class.php
 * - Interface files named InterfaceName.class.php
 * - Library files name LibraryName.lib.php
 * - Task files named TasksName.task.php
 * - Trait files named TraitName.trait.php
 * The loader can also automatically include php files without suffix in their path.
 */
class Loader
{
    /**
     * @brief Include a file associated with the given undefined class.
     * @param String $name The full name (namespace + name) of the undefined class.
     */
    public static function autoload($name)
    {
        // Create the file path from the namespace.
        $relativePath = str_replace('\\', DIRECTORY_SEPARATOR, $name);

        // Load the file.
        self::load($relativePath);
    }

    /**
     * @brief Include a file from its name.
     * @param String $name The file name.
     *
     * @exception Kernel::Exceptions::LoaderException When it's impossible to recognize a valid file path pattern.
     */
    public static function load($name)
    {
        // Create the file path.
        $path = ROOT_PATH.$name;

        // Create different file paths from different extensions.
        $pathClass = $path.'.class.php';
        $pathInterface = $path.'.interface.php';
        $pathLib = $path.'.lib.php';
        $pathTrait = $path.'.trait.php';
        $pathTask = $path.'.task.php';
        $pathClassic = $path.'.php';
        
        // Whether the file is associated with a class.
        if(file_exists($pathClass))
        {
            // Include the class.
            require_once($pathClass);
        }
        // Whether the file is associated with a trait.
        elseif(file_exists($pathTrait))
        {
            // Include the trait.
            require_once($pathTrait);
        }
        // Whether the file is associated with an interface.
        elseif(file_exists($pathInterface))
        {
            // Include the interface.
            require_once($pathInterface);
        }
        // Whether the file is associated with a task.
        elseif(file_exists($pathTask))
        {
            // Include the task.
            require_once($pathTask);
        }
        // Whether the file is associated with a library.
        elseif(file_exists($pathLib))
        {
            // Include the library.
            require_once($pathLib);
        }
        // Whether the file doesn't have a suffix pattern.
        elseif(file_exists($pathClassic))
        {
            // Include the file.
            require_once($pathClassic);
        }
        else
        {
            throw new Exceptions\LoaderException('It\'s impossible to load '.$path.'.');
        }
    }
}

?>