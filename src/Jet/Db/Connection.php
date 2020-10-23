<?php

namespace Jet\Db;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    /**
     * @var PDO|null
     */
    private $connection;
    /**
     * @var PDOException|null
     */
    private $lastPdoException;
    /**
     * @var string
     */
    private $driver;

    /**
     * Connection constructor.
     * @param string $driver
     * @throws Exception
     */
    function __construct($driver)
    {
        if(! $this->driverExists($driver)) throw new Exception('Driver is not founded');

        $this->driver = $driver;
        $this->lastPdoException = null;
        $this->connection = null;
    }

    private function driverExists($driver)
    {
        $drivers = PDO::getAvailableDrivers();
        return in_array(str_replace('pdo_', '', strtolower($driver)), $drivers);
    }

    /**
     * @param string $db
     * @param string|null $user
     * @param string|null $password
     * @param string|null $host
     * @param int|null $port
     * @return array
     * @throws Exception
     */
    private function getDriverConnectionParams($db, $user, $password, $host, $port)
    {
        switch (strtolower($this->driver)) {
            case 'pdo_mysql':
                return [ "mysql:host=$host;dbname=$db", $user, $password ];
            case 'pdo_pgsql':
                if($port === null) $port = 5432;
                return [ "pgsql:host=$host;port=$port;dbname=$db;user=$user;password=$password" ];
            case 'pdo_sqlsrv':
                if($port === null) $port = 1433;
                return [ "sqlsrv:Server=$host,$port;Database=$db", $user, $password ];
            case 'pdo_oci':
                if($port === null) $port = 1521;
                return [ "oci:dbname=//$host:$port/$db", $user, $password ];
            case 'pdo_sqlite':
                return [ "sqlite:$db" ];
        }

        throw new Exception('Unsupported driver');
    }

    /**
     * @return PDOException|null
     */
    function getLastException()
    {
        return $this->lastPdoException;
    }

    /**
     * @param string $db
     * @param string|null $user
     * @param string|null $password
     * @param string $host
     * @param int|null $port
     * @return bool
     */
    function connect($db, $user = null, $password = null, $host = '127.0.0.1', $port = null)
    {
        try {
            $params = $this->getDriverConnectionParams($db, $user, $password, $host, $port);
            $this->connection = new PDO(...$params);
            return true;
        }
        catch(PDOException $e) {
            $this->lastPdoException = $e;
            return false;
        }
        catch (Exception $e) {
            $this->lastPdoException = $e;
            return false;
        }
    }

    /**
     * @param string $table
     * @param array $data
     * @return string|null
     * @throws Exception
     */
    function insert($table, array $data)
    {
        if($this->connection === null) throw new Exception('Unconnect to database');
        $columns = implode(', ', array_keys($data));
        $values = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO `$table` ($columns) VALUES ($values)";
        $stmt = $this->connection->prepare($sql);
        try {
            $this->connection->beginTransaction();
            $stmt->execute(array_values($data));
            $id = $this->connection->lastInsertId();
            $this->connection->commit();
            return $id;
        }
        catch(PDOException $e) {
            $this->lastPdoException = $e;
            $this->connection->rollback();
            return null;
        }
    }

    /**
     * @param string $table
     * @param array $data
     * @param string $where
     * @return bool
     * @throws Exception
     */
    function update($table, array $data, $where)
    {
        if($this->connection === null) throw new Exception('Unconnect to database');
        $values = array_values($data);

        $setsArray = [];
        foreach ($data as $column => $value) {
            $setsArray[] = "`$column` = ?";
        }

        $sets = implode(', ', $setsArray);
        $sql = "UPDATE `$table` SET $sets WHERE $where;";
        $stmt = $this->connection->prepare($sql);
        try {
            $this->connection->beginTransaction();
            $stmt->execute($values);
            $this->connection->commit();
            return true;
        }
        catch(PDOException $e) {
            $this->lastPdoException = $e;
            $this->connection->rollback();
            return false;
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return PDOStatement|false
     */
    function query($sql, array $params = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }
        catch (PDOException $e) {
            $this->lastPdoException = $e;
            return false;
        }
    }
}