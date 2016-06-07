<?php

namespace Kernel\Core;

/**
 * @brief This bridge allows to link model objects and data sources.
 *
 * @details
 * The link is ensured by CRUD methods :
 * - C means Create and is associated with the insertOne method of the data source and the insert method of the model object.
 * - R means Read and is associated with the selectOne method of the data source and the load method of the model object.
 * - U means Update and is associated with the updateOne method of the data source and the update method of the model object.
 * - D means Delete and is associated with the deleteOne method of the data source and the remove method of the model object.
 *
 * @see Kernel::Core::DataSourceProxy.
 * @see Kernel::Core::Model.
 */
class MDSBridge
{
    /**
     * @brief Insert a model object through the data source.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the operation is successful.
     *
     * @details
     * Insertion can fill model object, typically the id attribute.
     */
    public static function insert(Model &$model, $sourceName = '')
    {
        return DataSourceProxy::get($sourceName)->insertOne($model);
    }
    
    /**
     * @brief Load a model object through the data source.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     */
    public static function load(Model &$model, $sourceName = '')
    {
        DataSourceProxy::get($sourceName)->selectOne($model);
    }
    
    /**
     * @brief Remove a model object through the data source.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the removing operation is successful.
     */
    public static function remove(Model $model, $sourceName = '')
    {
        return DataSourceProxy::get($sourceName)->deleteOne($model);
    }

    /**
     * @brief Update a model object through the data source.
     * @param Kernel::Core::Model $model The model object.
     * @param String $sourceName The data source name.
     * @return Bool Whether the updating operation is successful.
     */
    public static function update(Model $model, $sourceName = '')
    {
        return DataSourceProxy::get($sourceName)->updateOne($model);
    }
}

?>