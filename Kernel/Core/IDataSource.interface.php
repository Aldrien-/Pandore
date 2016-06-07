<?php

namespace Kernel\Core;

/**
 * @brief This interface defines minimal requirements for data sources to ensure its compatibility with models.
 *
 * @details
 * This compatibility is ensured by CRUD methods :
 * - C means Create and is associated with the insertOne method.
 * - R means Read and is associated with the selectOne method.
 * - U means Update and is associated with the updateOne method.
 * - D means Delete and is associated with the deleteOne method.
 *
 * @see Kernel::Core::Model.
 */
interface IDataSource
{
    /**
     * @brief Constructor.
     * @param String $DSN The DSN.
     * 
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function __construct($DSN);

    /**
     * @brief Delete data from model object.
     * @param Kernel::Core::Model $model The model object.
     * @return Bool Whether the operation is successful.
     */
    public function deleteOne($model);

    /**
     * @brief Create data from model object.
     * @param Kernel::Core::Model $model The model object.
     * @return Bool Whether the operation is successful.
     *
     * @details Insertion can fill model object, typically the id attribute.
     */
    public function insertOne(&$model);
    
    /**
     * @brief Read data and fill model object.
     * @param Kernel::Core::Model $model The model object.
     */
    public function selectOne(&$model);
    
    /**
     * @brief Update data from model object.
     * @param Kernel::Core::Model $model The model object.
     * @return Bool Whether the operation is successful.
     */
    public function updateOne($model);
}

?>