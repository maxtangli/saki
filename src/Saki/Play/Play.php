<?php
namespace Saki\Play;

use Saki\Game\Round;

/**
 * @package Saki\Play
 */
class Play {
    private $round;
    private $userSerializers;

    function __construct() {
        $this->round = new Round();
        $this->userSerializers = new \SplObjectStorage();
    }

    /**
     * @return Round
     */
    function getRound() {
        return $this->round;
    }

    /**
     * @return array [$userKey, ...]
     */
    function getRegisters() {
        return iterator_to_array($this->userSerializers);
    }

    /**
     * @param $userKey
     * @param Role $role
     */
    function register($userKey, Role $role) {
        $serializer = new RoundSerializer($this->getRound(), $role);
        $this->userSerializers[$userKey] = $serializer;
    }

    /**
     * @param $userKey
     */
    function unRegister($userKey) {
        unset($this->userSerializers[$userKey]);
    }

    /**
     * @param $userKey
     * @return RoundSerializer
     */
    private function getSerializer($userKey) {
        return $this->userSerializers[$userKey];
    }

    /**
     * @param $userKey
     * @return Role
     */
    private function getRole($userKey) {
        return $this->getSerializer($userKey)->getRole();
    }

    /**
     * @param $userKey
     * @return array
     */
    function getJson($userKey) {
        return $this->getSerializer($userKey)->toAllJson();
    }

    /**
     * @param $userKey
     * @param string $commandLine
     */
    function tryExecute($userKey, string $commandLine) {
        // todo check role

        $this->getRound()->processLine($commandLine);
    }
}