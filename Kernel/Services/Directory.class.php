<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class provides an access to a directory.
 *
 * @see Kernel::Services::Tools.
 */
class Directory
{
    use Tools;

    /**
     * @brief The directory resource.
     * @var Resource.
     */
	private $dir;
    /**
     * @brief The path.
     * @var String.
     */
	private $path;
	
    /**
     * @brief Constructor.
     * @param String $path The directory path.
     *
     * @exception Kernel::Exceptions::DirectoryException When the path isn't a valid directory path.
     */
	public function __construct($path)
	{
        // Set the directory path.
        $this->path = $path;

        // If the path isn't a directory.
        if(!is_dir($this->path))
        {
            throw new Exceptions\DirectoryException('"'.$this->path.'" isn\'t a directory.');
        }

        // Ensures there is a directory separator at the end of the path.
        if($this->path[strlen($this->path) - 1] != DIRECTORY_SEPARATOR)
        {
            $this->path .= DIRECTORY_SEPARATOR;
        }
	}
    
    /**
     * @brief Apply a callback on directory files.
     * @param Function $callback The callback.
     * @param Array $authorizedExt The authorized extension array.
     * @param Array $prohibitedExt The prohibited extension array.
     * @param Bool $isRecursive Whether the callback must be apply recursively.
     *
     * @details
     * The callback must be defined with the following format :
     *     @param String $directoryPath The directory path.
     *     @param String $relativefilePath The relative file path.
     *     
     * Extension arrays must be formatted like array('.php', '.html').
     * A empty authorized array means all extensions are authorized.
     *
     * @see Kernel::Services::Directory::applyRecursiveCallbackOnFiles.
     * @see http://fr2.php.net/manual/en/function.readdir.php
     */
    public function applyCallbackOnFiles($callback, $authorizedExt = array(), $prohibitedExt = array(), $isRecursive = false)
    {
        // Whether the callback has to be recursively applied. 
        if($isRecursive)
        {
            // Apply recursively the callback.
            $this->applyRecursiveCallbackOnFiles($callback, $authorizedExt, $prohibitedExt, $this->path);
        }
        else
        {
            // Open the directory.
            $this->open();

            while(false !== ($file = readdir($this->dir)))
            {
                // Ensures that what is read is an authorized file.
                if($file != '.' && $file != '..' && !is_dir($this->path.$file) && (empty($authorizedExt) || (!empty($authorizedExt) && in_array(strrchr($file, '.'), $authorizedExt))) && !in_array($strrchr($file, '.'), $prohibitedExt))
                {
                    // Apply the callback on the file.
                    $callback($this->path, $file);
                }
            }

            // Close the directory.
            $this->close();
        }
    }
	
    /**
     * @brief Get filenames which are in the directory.
     * @param Array $authorizedExt The authorized extensions array.
     * @param Array $prohibitedExt The prohibited extensions array.
     * @return ArrayObject The files name array.
     *
     * @details
     * A empty authorized array means all extensions are authorized.
     * 
     * Use :
     * - getFiles(array('.php, .html'), array('.js', '.css')).
     * 
     * @see http://fr2.php.net/manual/en/function.readdir.php
     */
	public function getFilesName($authorizedExt = array(), $prohibitedExt = array())
	{
        // Create the filenames container.
        $files = new \ArrayObject();

        // Open the directory.
        $this->open();

		while(false !== ($file = readdir($this->dir)))
        {
            // Ensures that what is read is an authorized file.
			if($file != '.' && $file != '..' && !is_dir($this->path.$file) && (empty($authorizedExt) || (!empty($authorizedExt) && in_array(strrchr($file, '.'), $authorizedExt))) && !in_array(strrchr($file, '.'), $prohibitedExt))
            {
                // Add the filename to the filenames container.
				$files[] = $file;
            }
        }

        // Close the directory.
        $this->close();

		return $files;
	}

    /**
     * @brief Get the directory name.
     * @return String The directory name.
     */
    public function getName()
    {
        $name = strrchr(substr($this->path, 0, -1), DIRECTORY_SEPARATOR);
        $name = ($name !== false) ? substr($name, 1) : $this->path;
        return $name;
    }
    
    /**
     * @brief Create a subdirectory.
     * @param String $path The sub directory path from the current directory.
     * @param Int $mode The chmod.
     * @param Bool $isRecursive Whether it is possible to create nested directories.
     * @return Bool Whether the creation is successful.
     *
     * @details
     * If the subdirectory has already been created, the creation will be declared as successful.
     */
    public function mkdir($path, $mode = 0777, $isRecursive = false)
	{
        // Whether the subdirectory has already been created.
		if(is_dir($this->path.$path))
        {
            return true;
        }
		else
        {
            return mkdir($this->path.$path, $mode, $isRecursive);
        }
	}
    
    /**
     * @brief Apply a callback on directory files with recursion.
     * @param Function $callback The callback.
     * @param Array $authorizedExt The authorized extension array.
     * @param Array $prohibitedExt The prohibited extension array.
     * @param String $originalPath The original directory path.
     *
     * @details
     * A empty authorized array means all extensions are authorized.
     *
     * @see http://fr2.php.net/manual/en/function.readdir.php
     */
    private function applyRecursiveCallbackOnFiles($callback, $authorizedExt, $prohibitedExt, $originalPath)
    {
        // Open the directory.
        $this->open();

        while(false !== ($file = readdir($this->dir)))
        {
            // If what is read is a directory.
            if(is_dir($this->path.$file))
            {
                // If what is read is a subdirectory.
                if($file != '.' && $file != '..')
                {
                    // Create a new sub directory instance.
                    $subDirectory = new Directory($this->path.$file);

                    // Apply the callback on this subdirectory.
                    $subDirectory->applyRecursiveCallbackOnFiles($callback, $authorizedExt, $prohibitedExt, $originalPath);
                }
            }
            // If what is read is an authorized file.
            elseif((empty($authorizedExt) || (!empty($authorizedExt) && in_array(strrchr($file, '.'), $authorizedExt))) && !in_array(strrchr($file, '.'), $prohibitedExt))
            {
                // Get the relative file path.
                $additionnalPath = $this->strcut($this->path, strlen($originalPath), strlen($this->path));

                // Apply the callback on the file.
                $callback($originalPath, $additionnalPath.$file);
            }
        }

        // Close the directory.
        $this->close();
    }

    /**
     * @brief Close the directory.
     */
    private function close()
    {
        closedir($this->dir);
    }

    /**
     * @brief Open the directory.
     *
     * @exception Kernel::Exceptions::DirectoryException When the directory is unopenable.
     */
    private function open()
    {
        // Open the directory.
        $this->dir = @opendir($this->path);
        
        // If the directory resource is invalid.
        if($this->dir === false)
        {
            throw new Exceptions\DirectoryException('Directory called "'.$this->path.'" is unopenable.');
        }
    }
}

?>