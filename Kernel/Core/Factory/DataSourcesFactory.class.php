<?php

namespace Kernel\Core\Factory;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class implements the factory pattern in order to build data sources.
 *
 * @see Kernel::Core::IDataSource.
 */
class DataSourcesFactory
{
    /**
     * @brief The data source interface name.
     * @var String.
     */
    private static $dataSourceInterfaceName = 'Kernel\Core\IDataSource';
    
    /**
     * @brief Build a data source from its class name.
     * @param String $className The class name of the data source.
     * @param String $DSN The DSN.
     * @return Kernel::Core::IDataSource The data source.
     *
     * @exception Kernel::Exceptions::DataSourcesFactoryException When the desired class doesn't exist.
     * @exception Kernel::Exceptions::DataSourcesFactoryException When the desired class doesn't implement the data source interface.
     * @exception Kernel::Exceptions::DataSourcesFactoryException When the data source isn't instantiable.
     */
    public static function get($className, $DSN)
    {        
        try {
            // Create a reflection class from the data source class.
            $reflectionClass = new \ReflectionClass($className);
        } catch(\Exception $e) {
            throw new Exceptions\DataSourcesFactoryException($className.' doesn\'t exist.');
        }
        
        // If the class doesn't implement the data source interface.
        if(!$reflectionClass->isSubclassOf(self::$dataSourceInterfaceName))
        {
            throw new Exceptions\DataSourcesFactoryException($className.' doesn\'t implement the data source interface.');
        }
        
        // If the class isn't instantiable.
        if(!$reflectionClass->isInstantiable())
        {
            throw new Exceptions\DataSourcesFactoryException($className.' isn\'t instantiable.');
        }
        
        return $reflectionClass->newInstance($DSN);
    }
}

?>