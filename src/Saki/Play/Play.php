<?php

namespace Saki\Play;

use Saki\Game\Round;
use Saki\Util\ArrayList;
use Saki\Util\Utils;

/**
 * @package Saki\Play
 */
class Play {
    private $round;
    private $roleManager;
    private $participants;

    function __construct() {
        $round = new Round();
        $round->enableDecider = true;
        $this->round = $round;
        $this->roleManager = new RoleManager($round->getRule()->getPlayerType());
        $this->participants = new \SplObjectStorage();
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
     * @param array|null $viewers
     * @return ArrayList ArrayList of Participant order by player ESWN.
     */
    function getParticipantList(array $viewers = null) {
        $actualViewers = isset($viewers) ? new ArrayList($viewers) : $this->getRound()->getRule()->getPlayerType()->getSeatWindList();
        $match = function (Participant $participant) use ($actualViewers) {
            $role = $participant->getRole();
            return $role->isPlayer() && $actualViewers->valueExist($role->getViewer());
        };
        $getOrderKey = function (Participant $participant) {
            return $participant->getRole()->getViewer()->getIndex();
        };
        $participantList = (new ArrayList($this->getUserKeys()))
            ->select([$this, 'getParticipant'])
            ->where($match)
            ->orderByAscending($getOrderKey);
        return $participantList;
    }

    /**
     * @return Participant
     */
    function getCurrentParticipant() {
        return $this->getParticipantList(
            [$this->getRound()->getCurrentSeatWind()]
        )->getSingle();
    }

    /**
     * @return ArrayList ArrayList of Participant order by player ESWN.
     */
    function getNotCurrentParticipantList() {
        return $this->getParticipantList(
            $this->getRound()->getNotCurrentSeatWindList()->toArray()
        );
    }

    /**
     * @param $userKey
     * @return Participant
     */
    function getParticipant($userKey) {
        if (!isset($this->participants[$userKey])) {
            throw new \InvalidArgumentException();
        }
        /** @var Participant $participant */
        $participant = $this->participants[$userKey];
        return $participant;
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
     * @param $userKeys
     */
    function joinAll($userKeys) {
        foreach ($userKeys as $userKey) {
            $this->join($userKey);
        }
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
        $roundJson = $this->getParticipant($userKey)
            ->getRoundSerializer()
            ->toAllJson();
        
        $keySelector = function (Participant $participant) {
            return $participant->getRole()->getViewer()->__toString();
        };
        $groups = $this->getParticipantList()->toGroups($keySelector);
        /** @var ArrayList $group */
        foreach ($groups as $key => $group) {
            $group = $group->toArray(Utils::getToStringCallback());
            $roundJson['areas'][$key]['participants'] = $group;
            $roundJson['areas'][$key]['profile'] = $group;
        }
        return $roundJson;
    }

    /**
     * @param $userKey
     * @param string $commandLine
     */
    function tryExecute($userKey, string $commandLine) {
        $role = $this->getParticipant($userKey)->getRole();
        if (!$role->isPlayer()) {
            throw new \InvalidArgumentException('not player.');
        }

        $requireActor = $role->getViewer();
        $allowDebugCommand = true; // todo
        $this->getRound()->getProcessor()->processLine($commandLine, $requireActor, $allowDebugCommand);
    }
}