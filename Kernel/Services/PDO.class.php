<?php

namespace Kernel\Services;

use Kernel\Exceptions as Exceptions;

/**
 * @brief This class is an adaptation of PHP Data Object for convenience use of PDO with Pandore.
 *
 * @details
 * This class simplifies the prepared queries mechanism using the executeQuery method. This method can be used through four methods associated with classic manipulations : DELETE, INSERT, SELECT, and UPDATE. These methods check queries type written by developpers and directly provide the result usually expected.
 *
 * @see Kernel::Services::Logger.
 */
class PDO extends \PDO
{
    use Tools;

    /**
     * @brief The delete query type.
     */
    const DELETE = 'DELETE';
    /**
     * @brief The insert query type.
     */
    const INSERT = 'INSERT';
    /**
     * @brief The select query type.
     */
    const SELECT = 'SELECT';
    /**
     * @brief The update query type.
     */
    const UPDATE = 'UPDATE';
    
    /**
     * @brief The database name.
     * @var String.
     */
    private $database;
    
    /**
     * @brief Constructor.
     * @param String $DSN The DSN.
     *
     * @exception Kernel::Exceptions::PDOException When something bad occurs about original PDO building.
     * 
     * @see http://php.net/manual/en/ref.pdo-mysql.connection.php.
     */
    public function __construct($DSN)
    {   
        // Convert DSN from string to array.
        $DSNArray = $this->DSNToArray($DSN);

        // Create a DSN string compliant with PDO.
        $DSN = $DSNArray->offsetGet('dbms').':host='.$DSNArray->offsetGet('host').';dbname='.$DSNArray->offsetGet('dbname');
        
        // Call the PDO constructor.
        try {
            parent::__construct($DSN, $DSNArray->offsetGet('username'), $DSNArray->offsetGet('password'));
            $this->database = $DSNArray->offsetGet('dbname');
        } catch(\PDOException $exception) {
            Logger::log('pdo', $exception->getMessage());

            throw new Exceptions\PDOException($exception->getMessage());
        }
    }
    
    /**
     * @brief Convert the object to a string.
     * @return String The string.
     * 
     * @see http://www.php.net/manual/en/language.oop5.magic.php#object.tostring.
     */
    public function __toString()
    {
        return print_r($this, true);
    }
    
    /**
     * @brief Execute a delete query.
     * @param String $query The query.
     * @param Array $queryData An array of data bound in the SQL statement.
     * @return Int The number of rows affected by the delete query.
     *
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain any delete instruction.
     */
    public function delete($query, $queryData = array())
    {
        // If the query isn't a delete one.
        if(stripos($query, self::DELETE) === false)
        {
            throw new Exceptions\PDOException('There is no delete instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData)->rowCount();
    }
    
    /**
     * @brief Execute a query.
     * @param String $query The query.
     * @param Array $queryData An array of data in the SQL statement.
     * @param Int $fetchStyle Controls how the next row will be returned.
     * @return PDOStatement The associated PDOStatement instance.
     *
     * @details
     * This method uses PDO prepared queries which secures database access.
     */
    public function executeQuery($query, $queryData, $fetchStyle = null)
    {
        // Prepare a query.
        $pdoStatement = $this->prepare($query);

        // Execute a prepared query.
        $success = $pdoStatement->execute($queryData);
        
        // Get the error array.
        $error = $pdoStatement->errorInfo();

        // If an error is dectected.
        if($error[0] != '00000')
        {
            // Create an error message.
            $message = 'Error '.$error[1].' : '.$error[2].' in '.$query.' with '.print_r($queryData, true);

            // Log the error message in the pdo.log file.
            Logger::log('pdo', $message);
        }
        
        return ($fetchStyle == null) ? $pdoStatement : $pdoStatement->fetchAll($fetchStyle);
    }

    /**
     * @brief Get the database name.
     * @return String The database name.
     */
    public function getDatabase()
    {
        return $this->database;
    }
    
    /**
     * @brief Get the last insert id.
     * @param String $name The sequence object name from which the Id should be returned.
     * @return String The last insert id.
     *
     * @see http://www.php.net/manual/en/pdo.lastinsertid.php.
     */
    public function getLastInsertId($name)
    {
        return $this->lastInsertId($name);
    }
    
    /**
     * @brief Execute an insert query.
     * @param String $query The query.
     * @param Array $queryData An array of data bound in the SQL statement.
     * @return Int The number of rows affected by the insert query.
     *
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain any insert instruction.
     */
    public function insert($query, $queryData = array())
    {
        // If the query isn't an insert one.
        if(stripos($query, self::INSERT) === false)
        {
            throw new Exceptions\PDOException('There is no insert instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData)->rowCount();
    }
    
    /**
     * @brief Execute a select query.
     * @param String $query The query.
     * @param Array $queryData An array of data bound in the SQL statement.
     * @param Int $fetchStyle Controls how the next row will be returned.
     * @return PDOStatement The associated PDOStatement instance.
     *
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain any select instruction.
     */
    public function select($query, $queryData = array(), $fetchStyle = null)
    {
        // If the query isn't a select one.
        if(stripos($query, self::SELECT) === false)
        {
            throw new Exceptions\PDOException('There is no select instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData, $fetchStyle);
    }
    
    /**
     * @brief Execute an update query.
     * @param String $query The query.
     * @param Array $queryData An array of data bound in the SQL statement.
     * @return Int The rows number affected by the update query.
     * 
     * @exception Kernel::Exceptions::PDOException When the query doesn't contain any update instruction.
     */
    public function update($query, $queryData = array())
    {
        // If the query isn't an update one.
        if(stripos($query, self::UPDATE) === false)
        {
            throw new Exceptions\PDOException('There is no update instruction in '.$query);
        }
        
        return $this->executeQuery($query, $queryData)->rowCount();
    }
}

?>