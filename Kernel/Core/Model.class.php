<?php

namespace Kernel\Core;

use Kernel\Services as Services;

/**
 * @brief This abstract class defines minimal requirements for each model object in order to ensure compatibility with data sources.
 *
 * @details
 * The compatibility is ensured by CRUD methods :
 * - C means Create and is associated with the insert method.
 * - R means Read and is associated with the load method.
 * - U means Update and is associated with the update method.
 * - D means Delete and is associated with the remove method.
 *
 * @see Kernel::Core::MDSBridge.
 * @see Kernel::Core::Tools.
 */
abstract class Model
{
    use Services\Tools;

    /**
     * @brief Insert a model object through the data source.
     * @param String $sourceName The data source name.
     * @return Bool Whether the inserting operation is successful.
     */
    public function insert($sourceName = '')
    {
        return MDSBridge::insert($this, $sourceName);
    }
    
    /**
     * @brief Load a model object through data source.
     * @param String $sourceName The data source name.
     */
    public function load($sourceName = '')
    {
        MDSBridge::load($this, $sourceName);
    }
    
    /**
     * @brief Remove a model object through the data source.
     * @param String $sourceName The data source name.
     * @return Bool Whether the removing operation is successful.
     */
    public function remove($sourceName = '')
    {
        return MDSBridge::remove($this, $sourceName);
    }
    
    /**
     * @brief Update a model object through the data source.
     * @param String $sourceName The data source name.
     * @return Bool Whether the updating operation is successful.
     */
    public function update($sourceName = '')
    {
        return MDSBridge::update($this, $sourceName);
    }
}

?>