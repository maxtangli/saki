<?php

namespace Saki\Play;

use Saki\Game\Round;
use Saki\Game\SeatWind;
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
     * @param SeatWind $viewer
     * @param bool $includeViewer
     * @param bool $isPlayer
     * @return ArrayList
     */
    function getParticipantList(SeatWind $viewer = null, bool $includeViewer = true, bool $isPlayer = null) {
        $match = function (Participant $participant) use ($viewer, $includeViewer, $isPlayer) {
            $role = $participant->getRole();
            $matchViewer = is_null($viewer) || ($role->isViewer($viewer) == $includeViewer);
            $matchPlayer = is_null($isPlayer) || $role->isPlayer() === $isPlayer;
            return $matchViewer && $matchPlayer;
        };
        $participantList = (new ArrayList($this->getUserKeys()))
            ->select([$this, 'getParticipant'])
            ->where($match);
        return $participantList;
    }

    /**
     * @param $userKey
     * @return Participant
     */
    function getParticipant($userKey) {
        if (!isset($this->participants[$userKey])) {
            throw new \InvalidArgumentException();
        }
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

        // temp
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
        // todo check role
        $this->getRound()->getProcessor()->processLine($commandLine);
    }
}