<?php

namespace App\Entities;

use Jet\Db\Entity;
use Jet\Db\Connection;
use Util\Validators\UserValidator;

class User extends Entity
{
	function __construct(Connection $db)
	{
		parent::__construct($db);
		$this->_validator = new UserValidator();
		$this->_tableName = 'User';
	}

	function save($data = [], $where = null)
	{
		// TODO: Implement save() method.
	}

}
