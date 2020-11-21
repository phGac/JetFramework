<?php

namespace Jet\Db;

use stdClass;
use Jet\Exception\Entity\EntityCreateFromFormError;

abstract class Entity
{
    protected $_db;
    protected $_validator;
    protected $_tableName;
    protected $_pk;

    function __construct(Connection $db)
    {
        $this->_db = $db;
        $this->_validator = null;
        $this->_tableName = null;
        $this->_pk = 'id';
    }

    function __set($name, $value)
    {
        switch ($name)
        {
            case '_db':
            case '_validator':
            case '_tableName':
                break;
            default:
                if(isset($this->$name))
                    $this->$name = $value;
                break;
        }
    }

    /**
     * @param array|stdClass $data
     * @param string|null $where
     * @return mixed
     */
    abstract function save($data = [], $where = null);

    /**
     * @param array $data
     * @return mixed
     * @throws EntityCreateFromFormError
     */
    function createFromForm($data)
    {
        $errors = $this->_validator->validate($data);
        if($errors) {
            throw new EntityCreateFromFormError($errors);
        }
        return $this->save($data);
    }

    /**
     * @param string|int $id
     * @return stdClass|null
     */
    function findByPk($id)
    {
        $result = $this->_db->query("SELECT * FROM `{$this->_tableName}`  WHERE `{$this->_pk}` = :id LIMIT 1;", [ ':id' => $id ]);
        if($result->rowCount() == 0) return null;
        else return $result->fetchObject();
    }
}