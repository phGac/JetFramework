<?php

namespace Jet\Db;

use PDO;
use PDOException;
use PDOStatement;

class Connection
{
    /**
     * @var PDO
     */
    private $connection;
    private $lastPdoException;

    /**
     * @return PDOException|null
     */
    function getLastException()
    {
        return $this->lastPdoException;
    }

    /**
     * @param string $db
     * @param string $user
     * @param string|null $password
     * @param string $host
     * @return bool
     */
    function connect($db, $user, $password = null, $host = '127.0.0.1')
    {
        try {
            $this->connection = new PDO("mysql:host=$host;dbname=$db", $user, $password);
            return true;
        }
        catch(PDOException $e) {
            $this->lastPdoException = $e;
            return false;
        }
    }

    /**
     * @param string $table
     * @param array $data
     * @return string|null
     */
    function insert($table, array $data)
    {
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
     */
    function update($table, array $data, $where)
    {
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
     * @return PDOStatement
     */
    function query($sql, array $params = [])
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}