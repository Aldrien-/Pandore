<?php

namespace Kernel\Core;

use Kernel\Exceptions as Exceptions;

/**
 * @brief The data source proxy manages data sources.
 *
 * @details
 * A valid data source object is an object whose class implements IDataSource interface.
 *
 * @see Kernel::Core::IDataSource.
 */
class DataSourceProxy
{
    /**
     * @brief The name of the default data source.
     */
    const DEFAULT_DS_NAME = 'Default';

    /**
     * @brief The project data sources namespace.
     * @var String.
     */
    private static $projectNamespace = 'Project\DataSources';
    /**
     * @brief The data sources array.
     * @var ArrayObject.
     */
    private static $sources = null;
    
    /**
     * @brief Initialize data sources declared in the configuration file.
     * @param ArrayObject $sources The data sources array.
     * @param ArrayObject $DSNs The DSN array.
     *
     * @exception DataSourceProxyException When the number of data source and DSN doesn't match.
     */
    public static function init(\ArrayObject $sources, \ArrayObject $DSNs)
    {
        // Whether the number of data source and DSN matches.
        if($sources->count() == $DSNs->count())
        {
            foreach($sources as $alias => $sourceName)
            {
                // Get the class name of the data source.
                $sourceClassName = self::$projectNamespace.'\\'.ucfirst($sourceName).'\\'.ucfirst($sourceName);

                // Build the data source object. 
                $dataSource = Factory\DataSourcesFactory::get($sourceClassName, $DSNs[$alias]);

                // Add the data source object.
                self::add($alias, $dataSource);
            }
        }
        else
        {
            throw new Exceptions\DataSourceProxyException('The number of source "'.$sources->count().'" and the number of DSN "'.$DSNs->count().'" doesn\'t match.');
        }
    }
    
    /**
     * @brief Add the data source.
     * @param String $name The name of data source.
     * @param Kernel::Core::IDataSource $dataSource The data source.
     */
    public static function add($name, IDataSource $dataSource)
    {
        // Get data sources.
        $sources = self::getSources();

        // Set the source ot its container.
        $sources->offsetSet($name, $dataSource);

        // Set the data sources.
        self::$sources = $sources;
    }
    
    /**
     * @brief Get a data source from its name.
     * @param String $name The name of data source.
     * @return Kernel::Core::IDataSource The data source.
     *
     * @details
     * In case of an empty name, the default source will be returned.
     *
     * @exception Kernel::Exceptions::DataSourceProxyException When DataSourceProxy doesn't know the desired data source.
     */
    public static function get($name = '')
    {
        // Get the data source name.
        $name = !empty($name) ? $name : self::DEFAULT_DS_NAME;
        
        // Get sources.
        $sources = self::getSources();

        // If the data source doesn't exist.
        if(!self::getSources()->offsetExists($name))
        {
            throw new Exceptions\DataSourceProxyException('DataSourceProxy doesn\'t know the data source called '.$name.'.');
        }

        return self::getSources()->offsetGet($name);
    }
    
    /**
     * @brief Get data sources array.
     * @return ArrayObject The data sources array.
     */
    private static function getSources()
    {
        // If the data sources doesn't exist yet.
        if(self::$sources === null)
        {
            // Create the data sources object.
            self::$sources = new \ArrayObject();
        }
        
        return self::$sources;
    }
}

?>