<?php

namespace Nodoka\server;

use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Nodoka\server
 */
class TableList {
    private $tableList;

    /**
     * @param int $tableCount
     */
    function __construct(int $tableCount = 0) {
        $idToTable = function ($id) {
            return new Table($id);
        };
        $tableIds = $tableCount > 0 ? range(0, $tableCount - 1) : [];
        $this->tableList = (new ArrayList($tableIds))
            ->select($idToTable);
    }

    /**
     * @return string
     */
    function __toString() {
        return $this->tableList->__toString();
    }

    /**
     * @return array
     */
    function toJson() {
        return $this->tableList->toArray(Utils::getMethodCallback('toJson'));
    }

    /**
     * @param int $id
     * @return Table
     */
    function getTableById($id) {
        return $this->tableList[$id];
    }

    /**
     * @param $userId
     * @return bool
     */
    function inTable($userId) {
        return $this->search($userId) !== false;
    }

    /**
     * @param $userId
     * @return User
     */
    function getUser($userId) {
        $result = $this->search($userId);
        if ($result === false) {
            throw new \InvalidArgumentException();
        }
        return $result['user'];
    }

    /**
     * @param $userId
     * @return Table
     */
    function getTable($userId) {
        $result = $this->search($userId);
        if ($result === false) {
            throw new \InvalidArgumentException();
        }
        return $result['table'];
    }

    /**
     * @param $userId
     * @return array|bool
     */
    private function search($userId) {
        /** @var Table $table */
        foreach ($this->tableList as $table) {
            $user = $table->getInTableUserOrFalse($userId);
            if ($user !== false) {
                return ['user' => $user, 'table' => $table];
            }
        }
        return false;
    }
}