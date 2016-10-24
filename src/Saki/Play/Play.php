<?php
namespace Saki\Play;

use Saki\Game\Round;

/**
 * @package Saki\Play
 */
class Play {
    private $round;
    private $participants;
    private $roleManager;

    function __construct() {
        $round = new Round();
        $this->round = $round;
        $this->participants = new \SplObjectStorage();
        $this->roleManager = new RoleManager($round->getRule()->getPlayerType());
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
    function getUserKeys() {
        return iterator_to_array($this->participants);
    }

    /**
     * @param $userKey
     * @return Participant
     */
    function getParticipant($userKey) {
        return $this->participants[$userKey];
    }

    /**
     * @param $userKey
     * @param Role $role
     */
    function join($userKey, Role $role = null) {
        $actualRole = $this->roleManager->assign($role);
        $serializer = new RoundSerializer($this->getRound(), $actualRole);
        $participant = new Participant($userKey, $actualRole, $serializer);
        $this->participants[$userKey] = $participant;
    }

    /**
     * @param $userKey
     */
    function leave($userKey) {
        $participant = $this->getParticipant($userKey);
        unset($this->participants[$userKey]);
        $this->roleManager->recycle($participant->getRole());
    }

    /**
     * @param $userKey
     * @return array
     */
    function getJson($userKey) {
        return $this->getParticipant($userKey)
            ->getRoundSerializer()
            ->toAllJson();
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