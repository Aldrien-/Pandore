<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class provides classic file services in object paradigm.
 */
class File
{
    /**
     * @brief The file resource.
     * @var Resource.
     */
    private $file;
    /**
     * @brief The opening mode.
     * @var String.
     */
    private $mode;
    /**
     * @brief The file path.
     * @var String.
     */
    private $path;
    
    /**
     * @brief Constructor.
     * @param String $path The file path.
     * @param String $mode The opening mode.
     *
     * @see http://php.net/manual/en/function.fopen.php.
     */
    public function __construct($path, $mode = 'r')
    {
        // Set main attributes.
        $this->path = $path;
        $this->mode = $mode;

        // Open the file.
        $this->open();
    }
    
    /**
     * @brief Destructor.
     */
    public function __destruct()
    {
        // Close the file.
        $this->close();
    }

    /**
     * @brief Change the file mode.
     * @param Int $mode The mode.
     * @return Whether the change is successful.
     */
    public function chmod($mode)
    {
        // Close the file.
        $this->close();

        // Change the file mode.
        $result = chmod($this->path, $mode);

        // Open the file.
        $this->open();

        return $result;
    }

    /**
     * @brief Close the file.
     * 
     * @exception Kernel::Exceptions::FileException When file can't be closed.
     */
    public function close()
    {
        // If the file resource is still valid.
        if(is_resource($this->file))
        {
            // If the file is can't be closed.
            if(fclose($this->file) === false)
            {
                throw new Exceptions\FileException('The file called "'.$this->path.'" can\'t be closed.');
            }
        }
    }

    /**
     * @brief Copy the file to the given destination.
     * @param String $destination The destination path.
     * @return Bool Whether the copy operation is successful.
     */
    public function copy($destination)
    {
        // Close the file.
        $this->close();

        // Copy the file.
        $result = copy($this->path, $destination);

        // Open the file.
        $this->open();

        return $result;
    }
    
    /**
     * @brief Get the file content.
     * @return String The file content.
     */
    public function getContent()
    {
        $str = '';
        $lines = file($this->path);
        foreach($lines as $line)
        {
            $str .= $line;
        }
        return $str;
    }

    /**
     * @brief Get line from file pointer and parse for CSV fields.
     * @param Int $length Must be greater than the longest line (in characters) to be found in the CSV file.
     * @param String $delimiter The delimiter character.
     * @param String $enclosure The enclosure character.
     * @param String $escape The escape character.
     * @return ArrayObject The reading result.
     * 
     * @see http://www.php.net/manual/en/function.fgetcsv.php
     */
    public function getCSVContent($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        $res = new \ArrayObject();

        while(($data = fgetcsv($this->file, $length, $delimiter, $enclosure, $escape)) !== false)
        {
            $res->append(new \ArrayObject($data));
        }

        return $res;
    }

    /**
     * @brief Get the file extension.
     * @return String The file extension.
     */
    public function getExtension()
    {
        if(($ext = strrchr($this->path, '.')) !== false)
        {
            return substr($ext, 1);
        }
        else
        {
            return null;
        }
    }
    
    /**
     * @brief Get file lines.
     * @return ArrayObject The file lines array.
     */
    public function getLines()
    {
        return new \ArrayObject(file($this->path));
    }
    
    /**
     * @brief Get the file name.
     * @param Bool $withExtension Whether the extension has to be part of it.
     * @return String The file name.
     */
    public function getName($withExtension = false)
    {
        // Get the file name with extension.
        $name = strrchr($this->path, DIRECTORY_SEPARATOR);
        $name = ($name !== false) ? substr($name, 1) : $this->path;

        // If the extension has to be removed.
        if(!$withExtension)
        {
            // Remove the extension from the file name.
            $name = $this->strcut($name, 0, strripos($name, '.') - 1);
        }

        return $name;
    }
    
    /**
     * @brief Get the file path.
     * @return String The file path.
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @brief Get the filesize.
     * @return Int The filesize
     */
    public function getSize()
    {
        return filesize($this->path);
    }
    
    /**
     * @brief Whether the file is readable.
     * @return Bool Whether the file is readable.
     */
    public function isReadable()
    {
        return is_readable($this->path);
    }
    
    /**
     * @brief Whether the file is writable.
     * @return Bool Whether the file is writable.
     */
    public function isWritable()
    {
        return is_writable($this->path);
    }

    /**
     * @brief Move the file.
     * @param String $newPath The new file path.
     * @return Bool Whether the move operation is successful.
     */
    public function move($newPath)
    {
        // Close the file.
        $this->close();

        // Rename the file.
        $result = rename($this->path, $newPath);

        // Set the new file path.
        $this->path = ($result !== false) ? $newPath : $this->path;

        // Open the file.
        $this->open();

        return $result;
    }

    /**
     * @brief Read the file in binary mode.
     * @param Int $length The number of read bytes.
     * @return Mixed The read string or false in failure case.
     */
    public function read($length)
    {
        return fread($this->file, $length);
    }
    
    /**
     * @brief Rename the file.
     * @param String $newName The new name (with extension).
     * @return Bool Whether the rename operation is successful.
     */
    public function rename($newName)
    {
        return $this->move($this->strcut($this->path, 0, strpos($this->path, $this->getName())).$newName);
    }
    
    /**
     * @brief Remove the file.
     * @return Bool Whether the remove operation is successful.
     */
    public function remove()
    {
        $this->close();
        return unlink($this->path);
    }
    
    /**
     * @brief Get stats informations about the file.
     * @return ArrayObject The stats informations about the file.
     */
    public function stats()
    {
        return new \ArrayObject(lstat($this->path));
    }
    
    /**
     * @brief Create an html link to file location.
     * @return String An html link to file location.
     */
    public function toHtml()
    {
        return '<a href=\'index.php?f='.$this->path.'\'>'.$this->getName().'</a>';
    }
    
    /**
     * @brief Write the given content into the file.
     * @param String $content The content.
     *
     * @exception Kernel::Exceptions::FileException When the file can't be written.
     * 
     * @see http://fr2.php.net/manual/en/function.fopen.php.
     */
    public function write($content)
    {
        // If the content can't be written into the file.
        if(fwrite($this->file, $content) === false)
        {
            throw new Exceptions\FileException('File called "'.$this->path.'" is unwritable with '.$this->mode.' mode.');
        }
    }

    /**
     * @brief Open the file.
     *
     * @exception Kernel::Exceptions::FileException When the file can't be opened.
     *
     * @see http://php.net/manual/en/function.fopen.php.
     */
    private function open()
    {
        // Open the file.
        $this->file = @fopen($this->path, $this->mode);
        
        // If the file resource is invalid.
        if($this->file === false)
        {
            throw new Exceptions\FileException('File called "'.$this->path.'" is unopenable with '.$this->mode.' mode.');
        }
    }
}

?>