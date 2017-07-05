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
    private $participants;

    function __construct(array $userKeys) {
        $round = new Round();
        $this->round = $round;
        $this->participants = new \SplObjectStorage();

        $this->joinAll($userKeys);
    }

    /**
     * @param $userKeys
     */
    private function joinAll($userKeys) {
        $round = $this->round;

        $userKeyList = new ArrayList($userKeys);

        $toRole = function (SeatWind $seatWind) use ($round) {
            return Role::createPlayer($round, $seatWind);
        };
        $roleList = $round->getRule()->getPlayerType()->getSeatWindList($toRole);

        $toParticipant = function ($userKey, Role $role) use ($round) {
            $serializer = new RoundSerializer($round, $role);
            return new Participant($userKey, $role, $serializer);
        };
        $participantList = (new ArrayList())->fromMapping($userKeyList, $roleList, $toParticipant);

        $registerParticipant = function (Participant $participant) {
            $this->participants[$participant->getUserKey()] = $participant;
        };
        $participantList->walk($registerParticipant);
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
        if (!isset($this->participants[$userKey])) {
            throw new \InvalidArgumentException();
        }
        /** @var Participant $participant */
        $participant = $this->participants[$userKey];
        return $participant;
    }

    /**
     * @param array|null $actors
     * @return ArrayList ArrayList of Participant order by player ESWN.
     */
    function getParticipantList(array $actors = null) {
        $actualActors = isset($actors) ? new ArrayList($actors) : $this->getRound()->getRule()->getPlayerType()->getSeatWindList();
        $match = function (Participant $participant) use ($actualActors) {
            $role = $participant->getRole();
            return $role->isPlayer() && $actualActors->valueExist($role->getActor());
        };
        $getOrderKey = function (Participant $participant) {
            return $participant->getRole()->getActor()->getIndex();
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
     * @return array
     */
    function getJson($userKey) {
        $roundJson = $this->getParticipant($userKey)
            ->getRoundSerializer()
            ->toAllJson();

        $keySelector = function (Participant $participant) {
            return $participant->getRole()->getActor()->__toString();
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
        $round = $this->getRound();

        $role = $this->getParticipant($userKey)->getRole();
        if (!$role->isPlayer()) {
            throw new \InvalidArgumentException('not player.');
        }

        $round->getProcessor()->processLine(
            $commandLine,
            $role->getActor(),
            $role->mayExecuteDebug()
        );
    }
}