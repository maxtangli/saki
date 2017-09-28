<?php

namespace Saki\Play;

use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Play
 */
class Seat {
    private $table;
    private $userProxy;
    private $role;
    private $roundSerializer;

    /**
     * @param Table $table
     * @param UserProxy $userProxy
     * @param Role $role
     */
    function __construct(Table $table, UserProxy $userProxy, Role $role) {
        $this->table = $table;
        $this->userProxy = $userProxy;
        $this->role = $role;
        $this->roundSerializer = new RoundSerializer($role);
    }

    /**
     * @return string
     */
    function __toString() {
        return sprintf('%s,%s', $this->getUserProxy()->getId(), $this->getRole());
    }

    /**
     * @return Table
     */
    function getTable() {
        return $this->table;
    }

    /**
     * @return UserProxy
     */
    function getUserProxy() {
        return $this->userProxy;
    }

    /**
     * @return Role
     */
    function getRole() {
        return $this->role;
    }

    /**
     * @return RoundSerializer
     */
    function getRoundSerializer() {
        return $this->roundSerializer;
    }

    /**
     * @return array
     */
    function getJson() {
        $roundJson = $this->getRoundSerializer()->toAllJson();

        $keySelector = function (Participant $participant) {
            return $participant->getRole()->getActor()->__toString();
        };
        $groups = $this->getTable()->getSeatList()->toGroups($keySelector);
        /** @var ArrayList $group */
        foreach ($groups as $key => $group) {
            $group = $group->toArray(Utils::getToStringCallback());
            $roundJson['areas'][$key]['participants'] = $group;
            $roundJson['areas'][$key]['profile'] = $group;
        }
        return $roundJson;
    }

    function notify() {
        $this->getUserProxy()->sendJson($this->getJson());
    }

    /**
     * @param string $commandLine
     */
    function tryExecute(string $commandLine) {
        $role = $this->getRole();
        if (!$role->isPlayer()) {
            throw new \InvalidArgumentException('not player.');
        }

        $round = $this->getTable()->getRound();
        $round->getProcessor()->processLine(
            $commandLine,
            $role->getActor(),
            $role->mayExecuteDebug()
        );

        $this->getTable()->notifyAll();
    }
}